/* global window */

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line
import availableMethodType from 'types/availableMethod';
import registeredMethodType from 'types/registeredMethod';
import LoadingIndicator from 'components/LoadingIndicator';

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
    };

    this.handleCompleteRegistration = this.handleCompleteRegistration.bind(this);
    this.handleCompleteProcess = this.handleCompleteProcess.bind(this);
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
   * @param {Object} method
   */
  getChooseMethodHandler(method) {
    return () => {
      this.setState({
        selectedMethod: method
      });
    };
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
    const { endpoints: { register }, backupMethod, onSetTitle } = this.props;
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

        // Set the title to blank
        onSetTitle('');

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
    const { endpoints: { complete } } = this.props;

    window.location = complete;
  }

  /**
   * Render a confirmation screen to inform the user that set up has been completed
   *
   * @return {HTMLElement}
   */
  renderCompleteScreen() {
    const { ss: { i18n } } = window;

    return (
      <div className="mfa-register-confirmation">
        <i className="font-icon-check-mark mfa-register-confirmation__icon" />
        <h2 className="mfa-register-confirmation__title">
          {i18n._t('MFARegister.SETUP_COMPLETE_TITLE', 'Multi-factor authentication is now set up')}
        </h2>
        <p className="mfa-register-confirmation__description">
          {i18n._t(
            'MFARegister.SETUP_COMPLETE_DESCRIPTION',
            'You can edit these settings from your profile area in the menu.'
          )}
        </p>
        <button
          onClick={this.handleCompleteProcess}
          className="mfa-register-confirmation__continue btn btn-primary"
        >
          {i18n._t('MFARegister.SETUP_COMPLETE_CONTINUE', 'Continue')}
        </button>
      </div>
    );
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
        <h2>{selectedMethod.name}</h2>
        <RegistrationComponent
          {...registerProps}
          method={selectedMethod}
          onCompleteRegistration={this.handleCompleteRegistration}
        />
      </div>
    );
  }

  /**
   * Get the support link as a "target=_blank" anchor tag from the given method (if one is set)
   *
   * @param {object} method
   * @return {HTMLElement|null}
   */
  renderSupportLink(method) {
    const { ss: { i18n } } = window;

    if (!method.supportLink) {
      return null;
    }

    return (
      <a
        href={method.supportLink}
        target="_blank"
        rel="noopener noreferrer"
      >
        {i18n._t('MFARegister.HELP', 'Find out more')}
      </a>
    );
  }

  /**
   * If the site has more than one multi factor method enabled, show others a user can register
   *
   * @return {HTMLElement|null}
   */
  renderOptions() {
    const { availableMethods } = this.props;
    const { selectedMethod } = this.state;

    // Don't render if there aren't methods or we already have a method registration in progress
    if (!availableMethods || selectedMethod) {
      return null;
    }

    return (
      <div>
        <h2>Register an authentication method</h2>
        <ul>
          {availableMethods.map(method => (
            <li key={method.urlSegment}>
              {method.description}
              <button onClick={this.getChooseMethodHandler(method)}>
                {method.name}
              </button>
              { this.renderSupportLink(method) }
            </li>
          ))}
        </ul>
      </div>
    );
  }

  render() {
    const { isComplete, selectedMethod } = this.state;

    if (isComplete) {
      return this.renderCompleteScreen();
    }

    if (selectedMethod) {
      return this.renderMethod();
    }

    return this.renderOptions();
  }
}

Register.propTypes = {
  availableMethods: PropTypes.arrayOf(availableMethodType),
  backupMethod: availableMethodType,
  endpoints: PropTypes.shape({
    register: PropTypes.string.isRequired,
    complete: PropTypes.string.isRequired,
  }),
  registeredMethods: PropTypes.arrayOf(registeredMethodType)
};

export default Register;
