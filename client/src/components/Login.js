/* global window */

import React, { Component, Fragment } from 'react';
import PropTypes from 'prop-types';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line
import registeredMethodType from 'types/registeredMethod';
import LoadingIndicator from 'components/LoadingIndicator';
import SelectMethod from 'components/Login/SelectMethod';

class Login extends Component {
  constructor(props) {
    super(props);

    this.state = {
      selectedMethod: null,
      loginProps: null,
      message: null,
      showOtherMethods: false,
    };

    this.handleCompleteLogin = this.handleCompleteLogin.bind(this);
    this.handleShowOtherMethodsPane = this.handleShowOtherMethodsPane.bind(this);
    this.handleHideOtherMethodsPane = this.handleHideOtherMethodsPane.bind(this);
    this.handleClickOtherMethod = this.handleClickOtherMethod.bind(this);
  }

  componentDidMount() {
    const { defaultMethod, registeredMethods, backupMethod } = this.props;

    // Choose either the default method or the first method in the list as the default login screen
    const defaultMethodDefinition = defaultMethod && registeredMethods.find(
      method => method.urlSegment === defaultMethod
    );

    if (defaultMethodDefinition) {
      this.setSelectedMethod(defaultMethodDefinition);
    } else {
      // TODO is this expected? We have the "first" method as the fallback default?
      // Use the first method that's not the backup method
      this.setSelectedMethod(backupMethod
        ? registeredMethods.find(method => method.urlSegment !== backupMethod.urlSegment)
        : registeredMethods[0]
      );
    }
  }

  componentDidUpdate(prevProps, prevState) {
    const { selectedMethod } = this.state;

    // If the selected method has changed (or been set for the first time) then we'll load a "start"
    // endpoint to get the process going
    if (
      (!prevState.selectedMethod && selectedMethod)
      || (prevState.selectedMethod
        && selectedMethod
        && prevState.selectedMethod.urlSegment !== selectedMethod.urlSegment
      )
    ) {
      this.fetchStartLoginData();
    }
  }

  /**
   * Set the current method the user will use to complete authentication
   *
   * @param {Object} method
   */
  setSelectedMethod(method) {
    this.setState({
      selectedMethod: method,
      // When a method is chosen we'll assume the "select other method" screen is not relevant now
      showOtherMethods: false,
    });
  }

  /**
   * Helper function to return methods aside from the selected one
   *
   * @return {Object[]}
   */
  getOtherMethods() {
    const { registeredMethods } = this.props;
    const { selectedMethod } = this.state;

    if (!selectedMethod) {
      return registeredMethods;
    }

    return registeredMethods.filter(method => method.urlSegment !== selectedMethod.urlSegment);
  }

  /**
   * Trigger a "fetch" of state for starting a login flow
   */
  fetchStartLoginData() {
    const { endpoints: { login } } = this.props;
    const { selectedMethod } = this.state;

    const endpoint = login.replace('{urlSegment}', selectedMethod.urlSegment);

    this.setState({
      loading: true,
    });

    // "start" a login
    fetch(endpoint).then(response => response.json().then(result => {
      this.setState({
        loading: false,
        loginProps: result,
      });
    }));
  }

  /**
   * Complete a login by verifying the given "loginData" with the "verify" endpoint
   *
   * @param {Object} loginData
   */
  handleCompleteLogin(loginData) {
    const { endpoints: { login }, onCompleteLogin } = this.props;
    const { selectedMethod } = this.state;
    const endpoint = login.replace('{urlSegment}', selectedMethod.urlSegment);

    this.setState({
      loading: true
    });

    // "verify" a login
    fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(loginData),
    })
      .then(response => {
        switch (response.status) {
          case 200:
            onCompleteLogin();
            return null;
          case 202:
            // TODO 202 is returned if multiple MFA methods are required...
            this.setState({
              loading: false,
            });
            return null;
          default:
        }
        return response.json();
      })
      .then(result => {
        if (result) {
          this.setState({
            loading: false,
            ...result,
          });
        }
      });
  }

  /**
   * Handle a click on a "More options" link to show other methods that have been registered
   *
   * @param {Event} event
   */
  handleShowOtherMethodsPane(event) {
    event.preventDefault();

    this.setState({
      showOtherMethods: true,
    });
  }

  /**
   * Handle a click on a "More options" link to show other methods that have been registered
   *
   * @param {Event} event
   */
  handleHideOtherMethodsPane(event) {
    event.preventDefault();

    this.setState({
      showOtherMethods: false,
    });
  }

  /**
   * Handle a click event on a button that will set the selected method of this login component. The
   * method specified should be the value of the target of the event (ie. the value of the button)
   *
   * @param {Event} event
   * @param method
   */
  handleClickOtherMethod(event, method) {
    event.preventDefault();
    const { registeredMethods } = this.props;

    if (method) {
      this.setSelectedMethod(
        registeredMethods.find(methodSpec => methodSpec.urlSegment === method.urlSegment)
      );
    }
  }

  /**
   * Render a control that will allow a user to display the "other methods" pane if the currently
   * selected method is not suitable
   *
   * @return {HTMLElement|null}
   */
  renderOtherMethodsControl() {
    const otherMethods = this.getOtherMethods();
    const { ss: { i18n } } = window;

    // There shouldn't be a control if there are no other methods to choose from
    if (!Array.isArray(otherMethods) || !otherMethods.length) {
      return null;
    }

    return (
      <a
        href="#"
        className="mfa-login__show-other-methods btn"
        onClick={this.handleShowOtherMethodsPane}
      >
        {i18n._t('MFALogin.MORE_OPTIONS', 'More options')}
      </a>
    );
  }

  /**
   * If the half-logged in member has more than one authentication method set up, show a list of
   * others they have enabled that could also be used to complete authentication and log in.
   *
   * @return {HTMLElement|null}
   */
  renderOtherMethods() {
    const otherMethods = this.getOtherMethods();
    const { selectedMethod, showOtherMethods } = this.state;
    const { resources } = this.props;

    if (selectedMethod && !showOtherMethods) {
      return null;
    }

    return (
      <SelectMethod
        resources={resources}
        methods={otherMethods}
        onClickBack={this.handleHideOtherMethodsPane}
        onSelectMethod={method => event => this.handleClickOtherMethod(event, method)}
      />
    );
  }

  /**
   * Render the component for the currently selected method
   *
   * @return {HTMLElement|null}
   */
  renderSelectedMethod() {
    const { selectedMethod, showOtherMethods, loginProps, message } = this.state;

    if (!selectedMethod || showOtherMethods) {
      return null;
    }

    const MethodComponent = loadComponent(selectedMethod.component);

    return (
      <div>
        <h2 className="mfa-section-title">{selectedMethod.leadInLabel}</h2>
        {MethodComponent && <MethodComponent
          {...loginProps}
          method={selectedMethod}
          error={message}
          onCompleteLogin={this.handleCompleteLogin}
          moreOptionsControl={this.renderOtherMethodsControl()}
        />}
      </div>
    );
  }

  render() {
    const { loading } = this.state;
    const { ss: { i18n } } = window;

    if (loading) {
      return <LoadingIndicator />;
    }

    return (
      <Fragment>
        <h1 className="mfa-app-title">{i18n._t('MFALogin.TITLE', 'Log in')}</h1>
        {this.renderSelectedMethod()}
        {this.renderOtherMethods()}
      </Fragment>
    );
  }
}

Login.propTypes = {
  // Endpoints that this app uses to communicate with the server
  endpoints: PropTypes.shape({
    login: PropTypes.string.isRequired,
    register: PropTypes.string,
  }),
  // An array of registered method definition objects
  registeredMethods: PropTypes.arrayOf(registeredMethodType),
  // The URL segment of the method to be used as default
  defaultMethod: PropTypes.string,
};

export default Login;
