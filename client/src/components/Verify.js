/* global window */

import React, { useState, useEffect } from 'react';
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

function Verify(props) {
  const {
    backupMethod,
    defaultMethod,
    endpoints,
    getUnavailableMessage,
    isAvailable,
    onCompleteVerification,
    registeredMethods,
    resources,
    SelectMethodComponent,
  } = props;
  const [loading, setLoading] = useState(false);
  const [selectedMethod, setSelectedMethod] = useState(null);
  const [verifyProps, setVerifyProps] = useState(null);
  const [message, setMessage] = useState(null);
  const [showOtherMethods, setShowOtherMethods] = useState(false);
  const [token, setToken] = useState(null);
  const i18n = window.ss.i18n;

  /**
   * Set the current method the user will use to complete authentication
   */
  function updateSelectedMethod(method) {
    setSelectedMethod(method);
    // When a method is chosen we'll assume the "select other method" screen is not relevant now
    setShowOtherMethods(false);
  }

  /**
   * Trigger a "fetch" of state for starting a verification flow
   */
  function fetchStartVerifyData() {
    const { verify } = endpoints;
    const endpoint = verify.replace('{urlSegment}', selectedMethod.urlSegment);
    setLoading(true);
    // "start" a verification
    api(endpoint).then(response => response.json().then(result => {
      const { SecurityID, ...other } = result;
      setLoading(false);
      setVerifyProps(other);
      setToken(SecurityID);
    }));
  }

  useEffect(() => {
    const defaultMethodDefinition = defaultMethod && registeredMethods.find(
      method => method.urlSegment === defaultMethod
    );
    if (defaultMethodDefinition) {
      updateSelectedMethod(defaultMethodDefinition);
    } else {
      // Use the first method that's not the backup method
      updateSelectedMethod(backupMethod
        ? registeredMethods.find(method => method.urlSegment !== backupMethod.urlSegment)
        : registeredMethods[0]
      );
    }
  }, [defaultMethod, registeredMethods, backupMethod]);

  // If the selected method has changed (or been set for the first time) then we'll load a "start"
  // endpoint to get the process going
  useEffect(() => {
    if (selectedMethod) {
      fetchStartVerifyData();
    }
  }, [selectedMethod]);

  /**
   * Helper function to return methods aside from the selected one
   */
  function getOtherMethods() {
    if (!selectedMethod) {
      return registeredMethods;
    }
    return registeredMethods.filter(method => method.urlSegment !== selectedMethod.urlSegment);
  }

  /**
   * Complete a verification by verifying the given "verifyData" with the "verify" endpoint
   */
  function handleCompleteVerification(verifyData) {
    const { verify } = endpoints;
    const params = token ? `?SecurityID=${token}` : '';
    const endpoint = `${verify.replace('{urlSegment}', selectedMethod.urlSegment)}${params}`;
    setLoading(true);
    // "complete" a verification
    api(endpoint, 'POST', JSON.stringify(verifyData))
      .then(response => {
        switch (response.status) {
          case 200:
            onCompleteVerification();
            return null;
          case 202:
            // 202 is returned if multiple MFA methods are required...
            setLoading(false);
            return null;
          case 429:
            setLoading(false);
            setMessage(i18n._t(
              'MultiFactorAuthentication.TRY_AGAIN_ERROR',
              fallbacks['MultiFactorAuthentication.TRY_AGAIN_ERROR']
            ));
            return null;
          default:
            if (response.status.toString().match(/^5[0-9]{2}$/)) {
              setLoading(false);
              setMessage(i18n._t(
                'MultiFactorAuthentication.UNKNOWN_ERROR',
                fallbacks['MultiFactorAuthentication.UNKNOWN_ERROR']
              ));
              return null;
            }
            return response.json();
        }
      })
      .then(result => {
        if (result) {
          setLoading(false);
          if (result.message) {
            setMessage(result.message);
          }
          if (result.verifyProps) {
            setVerifyProps(result.verifyProps);
          }
          if (result.selectedMethod) {
            updateSelectedMethod(result.selectedMethod);
          }
          if (result.showOtherMethods) {
            setShowOtherMethods(result.showOtherMethods);
          }
          if (result.token) {
            setToken(result.token);
          }
        }
      });
  }

  /**
   * Handle a click on a "More options" link to show other methods that have been registered,
   * and clear any verify component validation errors.
   */
  function handleShowOtherMethodsPane(event) {
    event.preventDefault();
    setShowOtherMethods(true);
    // Reset error states
    setMessage(message);
  }

  /**
   * Handle a click on a "More options" link to show other methods that have been registered
   */
  function handleHideOtherMethodsPane(event) {
    event.preventDefault();
    setShowOtherMethods(false);
  }

  /**
   * Handle a click event on a button that will set the selected method of this verify component.
   * The method specified should be the value of the target of the event (ie. the value of the
   * button)
   */
  function handleClickOtherMethod(event, method) {
    event.preventDefault();
    if (method) {
      updateSelectedMethod(
        registeredMethods.find(methodSpec => methodSpec.urlSegment === method.urlSegment)
      );
    }
  }

  /**
   * Render a control that will allow a user to display the "other methods" pane if the currently
   * selected method is not suitable
   */
  function renderOtherMethodsControl(extraClass = '') {
    const otherMethods = getOtherMethods();
    // There shouldn't be a control if there are no other methods to choose from
    if (!Array.isArray(otherMethods) || !otherMethods.length) {
      return null;
    }
    return <a
      href="#"
      className={classnames('btn btn-secondary', extraClass)}
      onClick={handleShowOtherMethodsPane}
    >
      {i18n._t('MFAVerify.MORE_OPTIONS', 'More options')}
    </a>;
  }

  function renderTitle() {
    return <h1 className="mfa-app-title">
      {i18n._t('MFAVerify.TITLE', 'Log in')}
    </h1>;
  }

  /**
   * If the half-logged in member has more than one authentication method set up, show a list of
   * others they have enabled that could also be used to complete authentication and log in.
   */
  function renderOtherMethods() {
    const otherMethods = getOtherMethods();
    if (selectedMethod && !showOtherMethods) {
      return null;
    }
    return <>
      {renderTitle()}
      <SelectMethodComponent
        resources={resources}
        methods={otherMethods}
        onClickBack={() => handleHideOtherMethodsPane()}
        onSelectMethod={method => event => handleClickOtherMethod(event, method)}
      />
    </>;
  }

  /**
   * Render the component for the currently selected method
   */
  function renderSelectedMethod() {
    if (!selectedMethod || showOtherMethods) {
      return null;
    }
    // Handle state where the method is not available to be used, e.g. if the browser
    // is incompatible or HTTPS is not enabled.
    if (isAvailable && !isAvailable(selectedMethod)) {
      const unavailableMessage = getUnavailableMessage(selectedMethod);
      return <LoadingError
        title={
          i18n._t(
            'MFAVerify.METHOD_UNAVAILABLE',
            'This authentication method is unavailable'
          )
        }
        message={unavailableMessage}
        controls={renderOtherMethodsControl('btn-outline-secondary')}
      />;
    }
    const MethodComponent = loadComponent(selectedMethod.component);
    const leadInLabel = i18n.inject(i18n._t('MFAVerify.VERIFY_WITH', 'Verify with {method}'), {
      method: selectedMethod.name.toLowerCase(),
    });
    return <>
      {renderTitle()}
      <h2 className="mfa-section-title">{leadInLabel}</h2>
      {MethodComponent && <MethodComponent
        {...verifyProps}
        method={selectedMethod}
        error={message}
        onCompleteVerification={() => handleCompleteVerification()}
        moreOptionsControl={renderOtherMethodsControl()}
      />}
    </>;
  }

  // Render the component
  if (loading) {
    return <LoadingIndicator block />;
  }
  return <>
    {renderSelectedMethod()}
    {renderOtherMethods()}
  </>;
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
