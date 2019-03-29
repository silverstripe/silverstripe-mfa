/* global window */

import React, { Component, Fragment } from 'react';
import PropTypes from 'prop-types';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line
import availableMethodType from 'types/availableMethod';
import registeredMethodType from 'types/registeredMethod';
import LoadingIndicator from 'components/LoadingIndicator';
import Introduction from 'components/Register/Introduction';
import Complete from 'components/Register/Complete';
import SelectMethod from 'components/Register/SelectMethod';

class Register extends Component {
  constructor(props) {
    super(props);

    const { registeredMethods, backupMethod } = props;

    // Set initial selected method value based on props...
    let selectedMethod = null;

    // Set the backup method as the "selected" method if there are methods already registered for
    // the user but one of those isn't the backup method.
    if (
      registeredMethods
      && registeredMethods.length
      && registeredMethods.filter(
          method => method.urlSegment === backupMethod.urlSegment
        ).length === 0
    ) {
      selectedMethod = backupMethod;
    }

    this.state = {
      selectedMethod,
      registerProps: null,
      isComplete: false,
      isStarted: false,
    };

    this.handleCompleteRegistration = this.handleCompleteRegistration.bind(this);
    this.handleCompleteProcess = this.handleCompleteProcess.bind(this);
    this.handleSelectMethod = this.handleSelectMethod.bind(this);
    this.handleSkip = this.handleSkip.bind(this);
    this.handleStart = this.handleStart.bind(this);
  }

  componentDidMount() {
    const { selectedMethod } = this.state;

    if (selectedMethod) {
      this.fetchStartRegistrationData();
    }
  }

  componentDidUpdate(prevProps, prevState) {
    const { selectedMethod } = this.state;

    if (!selectedMethod) {
      return;
    }

    // Trigger an async update of state if the selected method has changed
    if (JSON.stringify(selectedMethod) !== JSON.stringify(prevState.selectedMethod)) {
      this.fetchStartRegistrationData();
    }
  }

  /**
   * Set the MFA method the user is registering for
   *
   * @param {Object} method
   */
  handleSelectMethod(method) {
    this.setState({
      selectedMethod: method
    });
  }

  /**
   * Trigger a "fetch" of state for starting a registration flow
   */
  fetchStartRegistrationData() {
    const { endpoints: { register } } = this.props;
    const { selectedMethod } = this.state;

    const endpoint = register.replace('{urlSegment}', selectedMethod.urlSegment);

    // "start" a registration
    fetch(endpoint).then(response => response.json().then(result => {
      this.setState(() => ({
        registerProps: result,
      }));
    }));
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
   * Provided to individual method components to be called when the registration process is
   * completed
   *
   * @param {object} registrationData
   */
  handleCompleteRegistration(registrationData) {
    // Clear out the register props now - any process that returns the user to the register screen
    // will need a new "start" call
    this.setState({
      registerProps: null,
    });

    // Send registration details to server
    const { endpoints: { register }, backupMethod } = this.props;
    const { selectedMethod } = this.state;
    fetch(register.replace('{urlSegment}', selectedMethod.urlSegment), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(registrationData),
    })
      .then(response => {
        response.json();
      })
      .then(() => {
        // If there's a backup method that's not registered then we initialise that
        if (
          this.shouldSetupBackupMethod()
          && selectedMethod.urlSegment !== backupMethod.urlSegment
        ) {
          this.setState({
            selectedMethod: backupMethod,
          });
          return;
        }

        this.setState({
          selectedMethod: null,
          isComplete: true,
        });
      });
  }

  /**
   * Handle an event triggered to complete the registration process
   */
  handleCompleteProcess() {
    window.location = this.props.endpoints.complete;
  }

  /**
   * Handle an event triggered to start the registration process (move past the Introduction UI)
   */
  handleStart() {
    this.setState({ isStarted: true });
  }

  /**
   * Handle an event triggered to skip the registration process
   */
  handleSkip() {
    window.location = this.props.endpoints.skip;
  }

  /**
   * Render the registration component of the currently selected method.
   *
   * @return {HTMLElement|null}
   */
  renderMethod() {
    const { selectedMethod, registerProps } = this.state;

    // Render nothing if there isn't a method chosen
    if (!selectedMethod) {
      return null;
    }

    // Render loading if we don't have props yet...
    if (!registerProps) {
      return <LoadingIndicator />;
    }

    const RegistrationComponent = loadComponent(selectedMethod.component);

    return (
      <div>
        <h2 className="mfa-section-title">{selectedMethod.name}</h2>
        <RegistrationComponent
          {...registerProps}
          method={selectedMethod}
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
    const { availableMethods } = this.props;
    const { selectedMethod } = this.state;

    // Don't render if there aren't methods or we already have a method registration in progress
    if (!availableMethods || selectedMethod) {
      return null;
    }

    return (
      <SelectMethod
        methods={availableMethods}
        onSelectMethod={this.handleSelectMethod}
        onClickBack={() => this.setState({ isStarted: false })}
      />
    );
  }

  render() {
    const { canSkip, resources } = this.props;
    const { isComplete, isStarted } = this.state;
    const { ss: { i18n } } = window;

    if (!isStarted) {
      return (
        <Fragment>
          <h1 className="mfa-app-title">{i18n._t('MFARegister.TITLE', 'Multi-factor authentication')}</h1>
          <Introduction
            canSkip={canSkip}
            onContinue={this.handleStart}
            onSkip={this.handleSkip}
            resources={resources}
          />
        </Fragment>
      );
    }

    if (isComplete) {
      return <Complete onComplete={this.handleCompleteProcess} />;
    }

    return (
      <Fragment>
        <h1 className="mfa-app-title">{i18n._t('MFARegister.TITLE', 'Multi-factor authentication')}</h1>
        {this.renderMethod()}
        {this.renderOptions()}
      </Fragment>
    );
  }
}

Register.propTypes = {
  availableMethods: PropTypes.arrayOf(availableMethodType),
  backupMethod: availableMethodType,
  canSkip: PropTypes.bool,
  endpoints: PropTypes.shape({
    register: PropTypes.string.isRequired,
    complete: PropTypes.string.isRequired,
    skip: PropTypes.string.isRequired,
  }),
  registeredMethods: PropTypes.arrayOf(registeredMethodType),
  resources: PropTypes.object,
};

export default Register;
