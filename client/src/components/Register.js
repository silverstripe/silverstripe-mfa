/* eslint-disable import/no-cycle */
/* global window */

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import api from 'lib/api';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line
import availableMethodType from 'types/availableMethod';
import registeredMethodType from 'types/registeredMethod';
import LoadingIndicator from 'components/LoadingIndicator';
import Introduction from 'components/Register/Introduction';
import Complete from 'components/Register/Complete';
import SelectMethod from 'components/Register/SelectMethod';
import { connect } from 'react-redux';
import { showScreen, chooseMethod, removeAvailableMethod } from 'state/mfaRegister/actions';
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
      token: null,
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
    api(endpoint).then(response => response.json().then(result => {
      const { SecurityID: token, ...registerProps } = result;
      this.setState({
        registerProps,
        token,
      });
    }));
  }

  /**
   * Send the user back to the registration method selection screen from inside one of the
   * method registration components
   */
  handleBack() {
    const { availableMethods, onShowIntroduction, onShowChooseMethod } = this.props;

    // If there's only one method installed, send the user back to the introduction screen
    if (availableMethods.length === 1 && onShowIntroduction) {
      return onShowIntroduction();
    }

    // Send the user back to the "choose method" screen, clearing any selected method props
    // and errors from the state
    this.setState({ registerProps: null });

    return onShowChooseMethod();
  }

  /**
   * Provided to individual method components to be called when the registration process is
   * completed
   *
   * @param {object} registrationData
   */
  handleCompleteRegistration(registrationData) {
    // Send registration details to server
    const {
      endpoints: { register },
      selectedMethod,
      onRemoveAvailableMethod,
      onRegister,
    } = this.props;

    const { token } = this.state;
    const params = token ? `?SecurityID=${token}` : '';

    api(
      `${register.replace('{urlSegment}', selectedMethod.urlSegment)}${params}`,
      'POST',
      JSON.stringify(registrationData),
    )
      .then(response => {
        switch (response.status) {
          case 201:
            // Clear out the register props now - any process that returns the user to the
            // register screen will need a new "start" call
            this.setState({
              registerProps: null,
            });

            // Trigger a given callback if provided
            if (typeof onRegister === 'function') {
              onRegister(selectedMethod);
            }

            // Update redux state so this method is no longer available
            onRemoveAvailableMethod(selectedMethod);

            // Continue to setting up a backup method if required...
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

          this.setState((prevState) => ({
            registerProps: {
              ...prevState.registerProps,
              error: formattedErrors,
            },
          }));
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
    const { canSkip, resources, endpoints: { skip }, showSubTitle, IntroductionComponent } = this.props;

    return (
      <IntroductionComponent
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
    const { selectedMethod, showSubTitle, TitleComponent } = this.props;
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
        { showSubTitle && <TitleComponent /> }
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
   * If the site has more than one multi-factor method enabled, show others a user can register
   *
   * @return {SelectMethod|null}
   */
  renderOptions() {
    const { availableMethods, showSubTitle, SelectMethodComponent } = this.props;

    return (
      <SelectMethodComponent
        methods={availableMethods}
        showTitle={showSubTitle}
      />
    );
  }

  render() {
    const { screen, onCompleteRegistration, showTitle, showSubTitle, completeMessage, CompleteComponent } = this.props;
    const { ss: { i18n } } = window;

    if (screen === SCREEN_COMPLETE) {
      return (
        <CompleteComponent
          showTitle={showSubTitle}
          onComplete={onCompleteRegistration}
          message={completeMessage}
        />
      );
    }

    let content;

    switch (screen) {
      case SCREEN_CHOOSE_METHOD:
        content = this.renderOptions();
        break;
      case SCREEN_REGISTER_METHOD:
        content = this.renderMethod();
        break;
      case SCREEN_INTRODUCTION:
      default:
        content = this.renderIntroduction();
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
  onRegister: PropTypes.func,
  onCompleteRegistration: PropTypes.func.isRequired,
  registeredMethods: PropTypes.arrayOf(registeredMethodType),
  resources: PropTypes.object,
  showTitle: PropTypes.bool,
  showSubTitle: PropTypes.bool,
  IntroductionComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
  SelectMethodComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
  CompleteComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
  TitleComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
};

Register.defaultProps = {
  resources: {},
  showTitle: true,
  showSubTitle: true,
  showIntroduction: true,
  IntroductionComponent: Introduction,
  SelectMethodComponent: SelectMethod,
  CompleteComponent: Complete,
  TitleComponent: Title
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
  onShowIntroduction: () => dispatch(showScreen(SCREEN_INTRODUCTION)),
  onShowComplete: () => dispatch(showScreen(SCREEN_COMPLETE)),
  onSelectMethod: method => dispatch(chooseMethod(method)),
  onShowChooseMethod: () => {
    // clear any existing methods from state
    dispatch(chooseMethod(null));
    dispatch(showScreen(SCREEN_CHOOSE_METHOD));
  },
  onRemoveAvailableMethod: method => dispatch(removeAvailableMethod(method)),
});

export { Register as Component };

export default connect(mapStateToProps, mapDispatchToProps)(Register);
