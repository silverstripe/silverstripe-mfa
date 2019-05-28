/* global window */

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line
import availableMethodType from 'types/availableMethod';
import registeredMethodType from 'types/registeredMethod';
import LoadingIndicator from 'components/LoadingIndicator';
import Introduction from 'components/Register/Introduction';
import Complete from 'components/Register/Complete';
import SelectMethod from 'components/Register/SelectMethod';
import { connect } from 'react-redux';
import { showScreen, chooseMethod } from 'state/mfaRegister/actions';
import Title from 'components/Register/Title';

const SCREEN_INTRODUCTION = 1;
const SCREEN_REGISTER_METHOD = 2;
const SCREEN_CHOOSE_METHOD = 3;
const SCREEN_COMPLETE = 4;

export {
  SCREEN_INTRODUCTION,
  SCREEN_REGISTER_METHOD,
  SCREEN_CHOOSE_METHOD,
  SCREEN_COMPLETE,
};

class Register extends Component {
  constructor(props) {
    super(props);

    this.state = {
      registerProps: null,
    };

    this.handleBack = this.handleBack.bind(this);
    this.handleCompleteRegistration = this.handleCompleteRegistration.bind(this);
    this.handleSkip = this.handleSkip.bind(this);
  }

  componentDidMount() {
    const { selectedMethod } = this.props;

    if (selectedMethod) {
      this.fetchStartRegistrationData();
    }
  }

  componentDidUpdate(prevProps) {
    const { selectedMethod } = this.props;

    if (!selectedMethod) {
      return;
    }

    // Trigger an async update of state if the selected method has changed
    if (JSON.stringify(selectedMethod) !== JSON.stringify(prevProps.selectedMethod)) {
      this.fetchStartRegistrationData();
    }
  }

  /**
   * If there's a backup method that's not registered then we initialise that
   */
  setupBackupMethod() {
    const { backupMethod, selectedMethod, onShowComplete, onSelectMethod } = this.props;

    if (
      this.shouldSetupBackupMethod()
      && selectedMethod.urlSegment !== backupMethod.urlSegment
    ) {
      onSelectMethod(backupMethod);
      return;
    }

    onShowComplete();
  }

  /**
   * Trigger a "fetch" of state for starting a registration flow
   */
  fetchStartRegistrationData() {
    const { endpoints: { register }, selectedMethod } = this.props;

    const endpoint = register.replace('{urlSegment}', selectedMethod.urlSegment);

    // "start" a registration
    fetch(endpoint).then(response => response.json().then(result => {
      this.setState(() => ({
        registerProps: result,
      }));
    }));
  }

  /**
   * Send the user back to the registration method selection screen from inside one of the
   * method registration components
   */
  handleBack() {
    this.clearRegistrationErrors();
    this.props.onShowChooseMethod();
  }

  /**
   * Provided to individual method components to be called when the registration process is
   * completed
   *
   * @param {object} registrationData
   */
  handleCompleteRegistration(registrationData) {
    // Send registration details to server
    const { endpoints: { register }, selectedMethod } = this.props;

    fetch(register.replace('{urlSegment}', selectedMethod.urlSegment), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(registrationData),
    })
      .then(response => {
        switch (response.status) {
          case 201:
            // Clear out the register props now - any process that returns the user to the
            // register screen will need a new "start" call
            this.setState({
              registerProps: null,
            });

            this.setupBackupMethod();
            return null;
          default:
        }
        return response.json();
      })
      .then(response => {
        // Failure states are captured here

        if (response && response.errors) {
          const formattedErrors = response.errors.join(', ');

          this.setState({
            registerProps: {
              ...this.state.registerProps,
              error: formattedErrors,
            },
          });
        }
      });
  }

  /**
   * Inspects the props and returns whether a back-up method should also be set up for this
   * registration flow.
   *
   * @return {boolean}
   */
  shouldSetupBackupMethod() {
    const { backupMethod, registeredMethods } = this.props;

    if (!backupMethod) {
      return false;
    }

    return !registeredMethods.find(method => method.urlSegment === backupMethod.urlSegment);
  }

  /**
   * Clear any error messages when going back from a method's Register component to the
   * Select Method screen. Note that this doesn't clear errors for components that manage their
   * own internal state view transitions, which must be handled internally by those components.
   */
  clearRegistrationErrors() {
    this.setState({
      registerProps: {
        ...this.state.registerProps,
        error: null,
      }
    });
  }

  /**
   * Handle an event triggered to skip the registration process
   */
  handleSkip() {
    const { skip } = this.props.endpoints;

    if (skip) {
      window.location = this.props.endpoints.skip;
    }
  }

  /**
   * Render the introduction splash screen for registering MFA methods
   *
   * @return {Introduction}
   */
  renderIntroduction() {
    const { canSkip, resources, endpoints: { skip }, showSubTitle } = this.props;

    return (
      <Introduction
        canSkip={skip && canSkip}
        onSkip={this.handleSkip}
        resources={resources}
        showTitle={showSubTitle}
      />
    );
  }

  /**
   * Render the registration component of the currently selected method.
   *
   * @return {HTMLElement|null}
   */
  renderMethod() {
    const { selectedMethod, showSubTitle } = this.props;
    const { registerProps } = this.state;

    // Render nothing if there isn't a method chosen
    if (!selectedMethod) {
      return null;
    }

    // Render loading if we don't have props yet...
    if (!registerProps) {
      return <LoadingIndicator block />;
    }

    const RegistrationComponent = loadComponent(selectedMethod.component);

    return (
      <div>
        { showSubTitle && <Title /> }
        <RegistrationComponent
          {...registerProps}
          method={selectedMethod}
          onBack={this.handleBack}
          onCompleteRegistration={this.handleCompleteRegistration}
        />
      </div>
    );
  }

  /**
   * If the site has more than one multi factor method enabled, show others a user can register
   *
   * @return {SelectMethod|null}
   */
  renderOptions() {
    const { availableMethods, showSubTitle } = this.props;

    return (
      <SelectMethod
        methods={availableMethods}
        showTitle={showSubTitle}
      />
    );
  }

  render() {
    const { screen, onCompleteRegistration, showTitle, showSubTitle } = this.props;
    const { ss: { i18n } } = window;

    if (screen === SCREEN_COMPLETE) {
      return <Complete showTitle={showSubTitle} onComplete={onCompleteRegistration} />;
    }

    let content;

    switch (screen) {
      default:
      case SCREEN_INTRODUCTION:
        content = this.renderIntroduction();
        break;
      case SCREEN_CHOOSE_METHOD:
        content = this.renderOptions();
        break;
      case SCREEN_REGISTER_METHOD:
        content = this.renderMethod();
        break;
    }

    return (
      <div>
        {
          showTitle && <h1 className="mfa-app-title">
            {i18n._t('MFARegister.TITLE', 'Multi-factor authentication')}
          </h1>
        }
        { content }
      </div>
    );
  }
}

Register.propTypes = {
  availableMethods: PropTypes.arrayOf(availableMethodType),
  backupMethod: availableMethodType,
  canSkip: PropTypes.bool,
  endpoints: PropTypes.shape({
    register: PropTypes.string.isRequired,
    skip: PropTypes.string,
  }),
  onCompleteRegistration: PropTypes.func.isRequired,
  registeredMethods: PropTypes.arrayOf(registeredMethodType),
  resources: PropTypes.object,
  showTitle: PropTypes.bool,
  showSubTitle: PropTypes.bool,
};

Register.defaultProps = {
  resources: {},
  showTitle: true,
  showSubTitle: true,
};

const mapStateToProps = state => {
  const source = state.mfaRegister || state;

  return {
    screen: source.screen,
    selectedMethod: source.method,
    availableMethods: source.availableMethods,
  };
};

const mapDispatchToProps = dispatch => ({
  onShowComplete: () => dispatch(showScreen(SCREEN_COMPLETE)),
  onSelectMethod: method => dispatch(chooseMethod(method)),
  onShowChooseMethod: () => dispatch(showScreen(SCREEN_CHOOSE_METHOD)),
});

export { Register as Component };

export default connect(mapStateToProps, mapDispatchToProps)(Register);
