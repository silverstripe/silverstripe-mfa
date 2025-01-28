---
title: Creating a new MFA method - front-end
---

# Creating a new MFA method - front-end

## Introduction

The MFA module provides a clear path for creating additional authentication methods. In this document we'll cover how to
implement the front-end portion of the required code, using the Basic Math method as an example. Some prior experience
with React / Redux is recommended.

The front-end components of MFA make use of [`react-injector`](https://github.com/silverstripe/react-injector/)
(Injector) to allow sharing of React components and Redux reducers between separate JS bundles. You can find more
documentation on the Injector API in the [Silverstripe docs](https://docs.silverstripe.org/en/developer_guides/customising_the_admin_interface/reactjs_redux_and_graphql/#the-injector-api).

You'll find it easiest to get up and running by matching the NPM dependencies and Webpack configuration used in the TOTP
and WebAuthn modules, with a single entry point that handles registering your components with Injector. We also suggest
making use of the i18n library, exposed to components as `window.ss.i18n`, and shown in the examples below.

## Create components

In order to handle both registration of your method, and authentication via it, you'll need to provide a component for
each. The Register and Verify components in the core MFA module are designed to fetch and render your component when the
user selects your method, either in the registration flow or when authenticating.

### Register

Your component for registration will need to accept a couple of key props:

- `onCompleteRegistration`: A callback that should be invoked when your registration process is complete. Pass in an
  object with any data that needs to be passed to your `RegisterHandlerInterface::register()` implementation to complete
  the registration process.
- `onBack`: A callback that should be invoked if the user wants to pick another method. We recommend rendering a 'Back'
  button in the same fashion as the TOTP / WebAuthn methods do.
- Any data you return from your `RegisterHandlerInterface::start()` implementation will also be provided to the
  component as props. For example, the TOTP module sends a code to expose in the UI for the user to scan as a QR code or
  enter manually into their authenticator app.

A Register component for Basic Math might look like this:

```js
import React, { Component } from 'react';

class BasicMathRegister extends Component {
  constructor(props) {
    super(props);

    this.state = {
      secret: '',
    };

    this.handleChange = this.handleChange.bind(this);
  }

  handleChange(event) {
    this.setState({ secret: event.target.value });
  }

  render() {
    const { onCompleteRegistration, onBack } = this.props;
    const { ss: { i18n } } = window;

    return (
      <div className="mfa-register-backup-codes__container">
        <label htmlFor="secret" className="form-label">Enter a secret number:</label>
        <input id="secret" type="text" value={this.state.secret} onChange={this.handleChange} />

        <button
          className="btn btn-primary"
          onClick={() => onCompleteRegistration({ number: this.state.secret })}
        >
          {i18n._t('MFABackupCodesRegister.FINISH', 'Finish')}
        </button>
        <button
          className="btn btn-secondary"
          onClick={() => onBack()}
        >
          {i18n._t('MFABackupCodesRegister.BACK', 'Back')}
        </button>
      </div>
    );
  }
}

export default Register;
```

### Verify

Your verification component will look similar to your registration one - it should accept the following props:

- `onCompleteVerification`: A callback that should be invoked when the user has completed the challenge presented, with
  any data that your `VerifyHandlerInterface::verify()` implementation needs to confirm the user's identity. **NOTE:**
  It is *imperative* that your backend code is involved in the verification process, as providing secrets to the browser
  or otherwise relying solely on it to approve the authentication can result in significant security flaws.
- `moreOptionsControl`: A React component to render in your UI, which presents a button for users to pick a different
  method to authenticate with. We recommend referencing the layout of the TOTP / WebAuthn implementations.
- Any data you return from your `VerifyHandlerInterface::start()` implementation will also be provided to the
  component as props. For example, the WebAuthn module sends a challenge for the security key to sign.

A Verify component for Basic Math might look like this:

```js
import React, { Component } from 'react';

class BasicMathVerify extends Component {
  constructor(props) {
    super(props);

    this.state = {
      answer: '',
    };

    this.handleChange = this.handleChange.bind(this);
  }

  handleChange(event) {
    this.setState({
      answer: event.target.value,
    });
  }

  renderQuestion() {
    const { numbers } = this.props;

    return `What's the sum of ${numbers.join(', ')} and your secret number?`;
  }

  render() {
    const { onCompleteVerification, moreOptionsControl, numbers } = this.props;
    const { ss: { i18n } } = window;

    if (!numbers) {
      return (
        <div>
          <h3>{i18n._t('BasicMathLogin.LOADING', 'Loading...')}</h3>
          { moreOptionsControl }
        </div>
      );
    }

    return (
      <div className="mfa-register-backup-codes__container">
        <label style={{ display: 'block' }} htmlFor="answer" className="form-label">{this.renderQuestion()}</label>
        <input id="answer" type="text" value={this.state.answer} onChange={this.handleChange} />
        <div>
          <button
            className="btn btn-primary"
            onClick={() => onCompleteVerification({ answer: this.state.answer })}
          >
            {i18n._t('BasicMathLogin.FINISH', 'Finish')}
          </button>
          { moreOptionsControl }
        </div>
      </div>
    );
  }
}

export default BasicMathVerify;
```

## Register components with `Injector`

In order for your components to be found and rendered by the MFA module, you'll need to register them with Injector.
Your JS entrypoint (the file Webpack is pointed at) should contain the following:

```js
import Injector from 'lib/Injector'; // available via expose-loader
import BasicMathRegister from './components/BasicMathRegister';
import BasicMathVerify from './components/BasicMathVerify';

// Injector expects dependencies to be registered during this event, and initialises itself afterwards
window.document.addEventListener('DOMContentLoaded', () => {
  Injector.component.registerMany({
    BasicMathRegister,
    BasicMathVerify,
  });
});
```

You can then specify the component names via `VerifyHandlerInterface::getComponent()` and
`RegisterHandlerInterface::getComponent()`, and MFA will render them when your method is selected.

## Method availability

If your method needs to rely on frontend environment state to determine whether it's available (such as the browser
being used), you can [define a Redux reducer](https://docs.silverstripe.org/en/developer_guides/customising_the_admin_interface/reactjs_redux_and_graphql/#using-injector-to-customise-redux-state-data)
that will initialise some "availability" information in the Redux store, which the MFA module will look for when it
determines whether a method is available to be used or not. For example:

```js
// webauthn-module/client/src/state/availability/reducer.js
export default (state = {}) => {
  const isAvailable = typeof window.AuthenticatorResponse !== 'undefined';
  const availability = isAvailable ? {} : {
    isAvailable,
    unavailableMessage: 'Not supported by your browser.',
  };

  return { ...state, ...availability };
};
```

You must register this reducer with Injector with a name that matches the pattern `[urlSegment]Availability`. This is
required for the MFA module to find this part of the redux state. For example:

```js
// webauthn-module/client/src/boot/index.js
import Injector from 'lib/Injector';
import reducer from 'state/availability/reducer';

export default () => {
  Injector.reducer.register('web-authnAvailability', reducer);
};
```

Any part of the MFA React application that has the `withMethodAvailability` [HOC](https://reactjs.org/docs/higher-order-components.html)
applied to it will now have access to use `this.props.isAvailable(method)` and `this.props.getUnavailableMessage(method)`
in order to get a compiled set of this information, giving priority to frontend methods defined via Redux, and falling
back to backend definitions that come from the method's schema during the app mount. For this reason, it is important
that any Redux reducers you define only contribute information when they need to, since information provided will
take priority over the backend method definitions if it exists.

If you need to determine the availability of your method via the backend, see [Creating a new MFA method: Backend](mfa-method-backend.md#method-availability)
