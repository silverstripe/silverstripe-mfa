/* global window */

import React, { Component, Fragment } from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line
import api from 'lib/api';
import registeredMethodType from 'types/registeredMethod';
import LoadingIndicator from 'components/LoadingIndicator';
import SelectMethod from 'components/Verify/SelectMethod';
import withMethodAvailability from 'state/methodAvailability/withMethodAvailability';
import LoadingError from 'components/LoadingError';

import fallbacks from '../../lang/src/en.json';

class Verify extends Component {
  constructor(props) {
    super(props);

    this.state = {
      selectedMethod: null,
      verifyProps: null,
      message: null,
      showOtherMethods: false,
      token: null,
    };

    this.handleCompleteVerification = this.handleCompleteVerification.bind(this);
    this.handleShowOtherMethodsPane = this.handleShowOtherMethodsPane.bind(this);
    this.handleHideOtherMethodsPane = this.handleHideOtherMethodsPane.bind(this);
    this.handleClickOtherMethod = this.handleClickOtherMethod.bind(this);
  }

  componentDidMount() {
    const {
      defaultMethod,
      registeredMethods,
      backupMethod,
    } = this.props;

    // Choose either the default method or the first method in the list as the default verify screen
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
      this.fetchStartVerifyData();
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
   * Trigger a "fetch" of state for starting a verification flow
   */
  fetchStartVerifyData() {
    const { endpoints: { verify } } = this.props;
    const { selectedMethod } = this.state;

    const endpoint = verify.replace('{urlSegment}', selectedMethod.urlSegment);

    this.setState({
      loading: true,
    });

    // "start" a verification
    api(endpoint).then(response => response.json().then(result => {
      const { SecurityID: token, ...verifyProps } = result;
      this.setState({
        loading: false,
        verifyProps,
        token,
      });
    }));
  }

  /**
   * Complete a verification by verifying the given "verifyData" with the "verify" endpoint
   *
   * @param {Object} verifyData
   */
  handleCompleteVerification(verifyData) {
    const { endpoints: { verify }, onCompleteVerification } = this.props;
    const { selectedMethod, token } = this.state;
    const params = token ? `?SecurityID=${token}` : '';
    const endpoint = `${verify.replace('{urlSegment}', selectedMethod.urlSegment)}${params}`;
    const { ss: { i18n } } = window;

    this.setState({
      loading: true
    });

    // "complete" a verification
    api(endpoint, 'POST', JSON.stringify(verifyData))
      .then(response => {
        switch (response.status) {
          case 200:
            onCompleteVerification();
            return null;
          case 202:
            // TODO 202 is returned if multiple MFA methods are required...
            this.setState({
              loading: false,
            });
            return null;
          case 429:
            this.setState({
              loading: false,
              message: i18n._t(
                'MultiFactorAuthentication.TRY_AGAIN_ERROR',
                fallbacks['MultiFactorAuthentication.TRY_AGAIN_ERROR']
              )
            });
            return null;
          default:
            if (response.status.toString().match(/^5[0-9]{2}$/)) {
              this.setState({
                loading: false,
                message: i18n._t(
                  'MultiFactorAuthentication.UNKNOWN_ERROR',
                  fallbacks['MultiFactorAuthentication.UNKNOWN_ERROR']
                )
              });
              return null;
            }
            return response.json();
        }
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
   * Handle a click on a "More options" link to show other methods that have been registered,
   * and clear any verify component validation errors.
   *
   * @param {Event} event
   */
  handleShowOtherMethodsPane(event) {
    event.preventDefault();

    this.setState({
      showOtherMethods: true,
      // Reset error states
      message: '',
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
   * Handle a click event on a button that will set the selected method of this verify component.
   * The method specified should be the value of the target of the event (ie. the value of the
   * button)
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
   * @param {string|null} extraClass Will be added to the button
   * @return {HTMLElement|null}
   */
  renderOtherMethodsControl(extraClass = '') {
    const otherMethods = this.getOtherMethods();
    const { ss: { i18n } } = window;

    // There shouldn't be a control if there are no other methods to choose from
    if (!Array.isArray(otherMethods) || !otherMethods.length) {
      return null;
    }

    return (
      <a
        href="#"
        className={classnames('btn btn-secondary', extraClass)}
        onClick={this.handleShowOtherMethodsPane}
      >
        {i18n._t('MFAVerify.MORE_OPTIONS', 'More options')}
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
    const { resources, SelectMethodComponent } = this.props;

    if (selectedMethod && !showOtherMethods) {
      return null;
    }

    return (
      <>
        {this.renderTitle()}
        <SelectMethodComponent
          resources={resources}
          methods={otherMethods}
          onClickBack={this.handleHideOtherMethodsPane}
          onSelectMethod={method => event => this.handleClickOtherMethod(event, method)}
        />
      </>
    );
  }

  /**
   * Render the component for the currently selected method
   *
   * @return {HTMLElement|null}
   */
  renderSelectedMethod() {
    const { isAvailable, getUnavailableMessage } = this.props;
    const { selectedMethod, showOtherMethods, verifyProps, message } = this.state;
    const { ss: { i18n } } = window;

    if (!selectedMethod || showOtherMethods) {
      return null;
    }

    // Handle state where the method is not available to be used, e.g. if the browser
    // is incompatible or HTTPS is not enabled.
    if (isAvailable && !isAvailable(selectedMethod)) {
      const unavailableMessage = getUnavailableMessage(selectedMethod);
      return (
        <LoadingError
          title={
            i18n._t(
              'MFAVerify.METHOD_UNAVAILABLE',
              'This authentication method is unavailable'
            )
          }
          message={unavailableMessage}
          controls={this.renderOtherMethodsControl('btn-outline-secondary')}
        />
      );
    }

    const MethodComponent = loadComponent(selectedMethod.component);
    const leadInLabel = i18n.inject(i18n._t('MFAVerify.VERIFY_WITH', 'Verify with {method}'), {
      method: selectedMethod.name.toLowerCase(),
    });

    return (
      <>
        {this.renderTitle()}

        <h2 className="mfa-section-title">{leadInLabel}</h2>
        {MethodComponent && <MethodComponent
          {...verifyProps}
          method={selectedMethod}
          error={message}
          onCompleteVerification={this.handleCompleteVerification}
          moreOptionsControl={this.renderOtherMethodsControl()}
        />}
      </>
    );
  }

  /**
   * @returns {HTMLElement}
   */
  renderTitle() {
    const { ss: { i18n } } = window;

    return (
      <h1 className="mfa-app-title">
        {i18n._t('MFAVerify.TITLE', 'Log in')}
      </h1>
    );
  }

  render() {
    const { loading } = this.state;

    if (loading) {
      return <LoadingIndicator block />;
    }

    return (
      <>
        {this.renderSelectedMethod()}
        {this.renderOtherMethods()}
      </>
    );
  }
}

Verify.propTypes = {
  // Endpoints that this app uses to communicate with the server
  endpoints: PropTypes.shape({
    verify: PropTypes.string.isRequired,
    register: PropTypes.string,
  }),
  // An array of registered method definition objects
  registeredMethods: PropTypes.arrayOf(registeredMethodType),
  // The URL segment of the method to be used as default
  defaultMethod: PropTypes.string,
  SelectMethodComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
};

Verify.defaultProps = {
  SelectMethodComponent: SelectMethod
};

export { Verify as Component };

export default withMethodAvailability(Verify);
