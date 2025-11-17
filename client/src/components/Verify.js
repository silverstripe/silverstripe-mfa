/* global window */

import React, { Fragment, useState, useEffect } from 'react';
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

const Verify = ({
  endpoints,
  registeredMethods,
  defaultMethod,
  SelectMethodComponent = SelectMethod,
  isAvailable,
  getUnavailableMessage,
  resources,
  backupMethod,
  onCompleteVerification,
}) => {
  const [selectedMethod, setSelectedMethodState] = useState(null);
  const [verifyProps, setVerifyProps] = useState(null);
  const [message, setMessage] = useState(null);
  const [showOtherMethods, setShowOtherMethods] = useState(false);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(false);

  /**
   * Set the current method the user will use to complete authentication
   *
   * @param {Object} method
   */
  const setSelectedMethod = (method) => {
    setSelectedMethodState(method);
    setShowOtherMethods(false);
  };

  /**
   * Helper function to return methods aside from the selected one
   *
   * @return {Object[]}
   */
  const getOtherMethods = () => {
    if (!selectedMethod) {
      return registeredMethods;
    }
    return registeredMethods.filter(method => method.urlSegment !== selectedMethod.urlSegment);
  };

  /**
   * Trigger a "fetch" of state for starting a verification flow
   */
  const fetchStartVerifyData = () => {
    const endpoint = endpoints.verify.replace('{urlSegment}', selectedMethod.urlSegment);
    setLoading(true);
    api(endpoint).then(response => response.json().then(result => {
      const { SecurityID: resultToken, ...resultVerifyProps } = result;
      setLoading(false);
      setVerifyProps(resultVerifyProps);
      setToken(resultToken);
    }));
  };

  /**
   * Complete a verification by verifying the given "verifyData" with the "verify" endpoint
   *
   * @param {Object} verifyData
   */
  const handleCompleteVerification = (verifyData) => {
    const params = token ? `?SecurityID=${token}` : '';
    const endpoint = `${endpoints.verify.replace('{urlSegment}', selectedMethod.urlSegment)}${params}`;
    const { ss: { i18n } } = window;

    setLoading(true);
    api(endpoint, 'POST', JSON.stringify(verifyData))
      .then(response => {
        switch (response.status) {
          case 200:
            onCompleteVerification();
            return null;
          case 202:
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
          setMessage(result.message || null);
        }
      });
  };

  /**
   * Handle a click on a "More options" link to show other methods that have been registered,
   * and clear any verify component validation errors.
   *
   * @param {Event} event
   */
  const handleShowOtherMethodsPane = (event) => {
    event.preventDefault();
    setShowOtherMethods(true);
    setMessage('');
  };

  /**
   * Handle a click on a "More options" link to show other methods that have been registered
   *
   * @param {Event} event
   */
  const handleHideOtherMethodsPane = (event) => {
    event.preventDefault();
    setShowOtherMethods(false);
  };

  /**
   * Handle a click event on a button that will set the selected method of this verify component.
   * The method specified should be the value of the target of the event (ie. the value of the
   * button)
   *
   * @param {Event} event
   * @param method
   */
  const handleClickOtherMethod = (event, method) => {
    event.preventDefault();
    if (method) {
      setSelectedMethod(
        registeredMethods.find(methodSpec => methodSpec.urlSegment === method.urlSegment)
      );
    }
  };

  /**
   * Render a control that will allow a user to display the "other methods" pane if the currently
   * selected method is not suitable
   *
   * @param {string|null} extraClass Will be added to the button
   * @return {HTMLElement|null}
   */
  const renderOtherMethodsControl = (extraClass = '') => {
    const otherMethods = getOtherMethods();
    const { ss: { i18n } } = window;

    if (!Array.isArray(otherMethods) || !otherMethods.length) {
      return null;
    }

    return (
      <a
        href="#"
        className={classnames('btn btn-secondary', extraClass)}
        onClick={handleShowOtherMethodsPane}
      >
        {i18n._t('MFAVerify.MORE_OPTIONS', 'More options')}
      </a>
    );
  };

  /**
   * @returns {HTMLElement}
   */
  const renderTitle = () => {
    const { ss: { i18n } } = window;
    return (
      <h1 className="mfa-app-title">
        {i18n._t('MFAVerify.TITLE', 'Log in')}
      </h1>
    );
  };

  /**
   * If the half-logged in member has more than one authentication method set up, show a list of
   * others they have enabled that could also be used to complete authentication and log in.
   *
   * @return {HTMLElement|null}
   */
  const renderOtherMethods = () => {
    const otherMethods = getOtherMethods();

    if (selectedMethod && !showOtherMethods) {
      return null;
    }

    return (
      <>
        {renderTitle()}
        <SelectMethodComponent
          resources={resources}
          methods={otherMethods}
          onClickBack={handleHideOtherMethodsPane}
          onSelectMethod={method => event => handleClickOtherMethod(event, method)}
        />
      </>
    );
  };

  /**
   * Render the component for the currently selected method
   *
   * @return {HTMLElement|null}
   */
  const renderSelectedMethod = () => {
    if (!selectedMethod || showOtherMethods) {
      return null;
    }

    const { ss: { i18n } } = window;

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
          controls={renderOtherMethodsControl('btn-outline-secondary')}
        />
      );
    }

    const MethodComponent = loadComponent(selectedMethod.component);
    const leadInLabel = i18n.inject(i18n._t('MFAVerify.VERIFY_WITH', 'Verify with {method}'), {
      method: selectedMethod.name.toLowerCase(),
    });

    return (
      <>
        {renderTitle()}

        <h2 className="mfa-section-title">{leadInLabel}</h2>
        {MethodComponent && <MethodComponent
          {...verifyProps}
          method={selectedMethod}
          error={message}
          onCompleteVerification={handleCompleteVerification}
          moreOptionsControl={renderOtherMethodsControl()}
        />}
      </>
    );
  };

  useEffect(() => {
    const defaultMethodDefinition = defaultMethod && registeredMethods.find(
      method => method.urlSegment === defaultMethod
    );

    if (defaultMethodDefinition) {
      setSelectedMethod(defaultMethodDefinition);
    } else {
      setSelectedMethod(backupMethod
        ? registeredMethods.find(method => method.urlSegment !== backupMethod.urlSegment)
        : registeredMethods[0]
      );
    }
  }, []);

  useEffect(() => {
    if (selectedMethod) {
      fetchStartVerifyData();
    }
  }, [selectedMethod]);

  if (loading) {
    return <LoadingIndicator block />;
  }

  return (
    <>
      {renderSelectedMethod()}
      {renderOtherMethods()}
    </>
  );
};

Verify.propTypes = {
  endpoints: PropTypes.shape({
    verify: PropTypes.string.isRequired,
    register: PropTypes.string,
  }),
  registeredMethods: PropTypes.arrayOf(registeredMethodType),
  defaultMethod: PropTypes.string,
  SelectMethodComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
};

export { Verify as Component };

export default withMethodAvailability(Verify);
