/* global window */

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { loadComponent } from 'lib/Injector';
import availableMethodType from 'types/availableMethod';


class Register extends Component {
  constructor(props) {
    super(props);
    this.state = {
      selectedMethod: null,
      registerProps: null,
    };

    this.handleCompleteRegistration = this.handleCompleteRegistration.bind(this);
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
   * Provided to individual method components to be called when the registration process is
   * completed
   */
  handleCompleteRegistration(registrationData) {
    // Clear out the register props now - any process that returns the user to the register screen
    // will need a new "start" call
    this.setState({
      registerProps: null,
    });

    // Send registration details to server
    const { endpoints: { register } } = this.props;
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
        this.setState({
          selectedMethod: null,
        });
        // TODO flesh out what happens here
      });
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
      return <div className="mfa__loader" />;
    }

    const RegistrationComponent = loadComponent(selectedMethod.component);

    return (
      <div>
        <h2>{selectedMethod.name}</h2>
        <RegistrationComponent
          {...registerProps}
          method={selectedMethod}
          handleCompleteRegistration={this.handleCompleteRegistration}
        />
      </div>
    );
  }

  /**
   * Get the support link as a "target=_blank" anchor tag from the given method (if one is set)
   *
   * @param method
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
        <h1>Register an authentication method</h1>
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
    return (
      <div>
        <div className="mfa__log-out">
          <a href="Security/logout">Log out</a>
        </div>
        { this.renderMethod() }
        { this.renderOptions() }
      </div>
    );
  }
}

Register.propTypes = {
  availableMethods: PropTypes.arrayOf(availableMethodType),
  endpoints: PropTypes.shape({
    register: PropTypes.string.isRequired,
  })
};

export default Register;
