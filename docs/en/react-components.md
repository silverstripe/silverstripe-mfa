# Front-end React components

This module provides two main React components that deal with the two facets of multi factor authentication: 
[Register](../../client/src/components/Register.js) and [Verify](../../client/src/components/Verify.js), along with a 
few components that handle configuration in the CMS.

## Configuring requirements

<!-- TODO: Information about Requirements and Injector -->

## Using `Register` and `Verify`

### Using the components

Refer to the prop type definitions and their documentation in the source code for each component, listed above.

### State management

The `Register` component manages its state with Redux. If this component is used within admin screens then the Redux 
reducer will already be registered with the store which will be automatically provided with the `Register` component so 
long as `Injector` is used. Actions available for the Redux store are defined and documented in 
[the source code](../../client/src/state/mfaRegister/actions.js).

<!-- TODO: Documentation for consuming Injector outside of admin context? -->

## Customising method availability

There are two ways to define whether an authentication method is available to be used.

### Backend

Backend implementations can define logic for this in `MethodInterface::isAvailable()`, for example:

```php
class MyMethod implements MethodInterface
{
    public function isAvailable(): bool
    {
        return Injector::inst()->get(HTTPRequest::class)->getHeader('something') === 'example';
    }

    public function getUnavailableMessage(): string
    {
        return 'My silly example criteria was not fulfilled, so you cannot use me.';
    }
}
```

Both of these methods are automatically exposed via the MFA application schema when the application is loaded, so
no extra work is required to incorporate these values.

### Frontend

If your method needs to rely on frontend environment state to determine whether it's available (such as the browser
being used), you can [define a Redux reducer](https://docs.silverstripe.org/en/4/developer_guides/customising_the_admin_interface/reactjs_redux_and_graphql/#using-injector-to-customise-redux-state-data)
that will initialise some "availability" information in the Redux store, which the MFA module will look for when it
determines whether a method is available to be used or not. For example:

```jsx
// File: webauthn-module/client/src/state/availability/reducer.js
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

```jsx
// File: webauthn-module/client/src/boot/index.js
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
