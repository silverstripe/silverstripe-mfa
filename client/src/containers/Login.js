/* global window */

import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import api from 'lib/api';
import Verify from 'components/Verify';
import Register from 'components/Register';
import LoadingIndicator from 'components/LoadingIndicator';
import LoadingError from 'components/LoadingError';
import { chooseMethod, setAvailableMethods } from 'state/mfaRegister/actions';
import { setAllMethods } from 'state/mfaVerify/actions';
import { connect } from 'react-redux';

/**
 * Directs the flow of the log in process.
 *
 * All information comes from a JSON schema fetched on mount {@see useEffect}
 *
 * This component will either render a verification screen or a registration screen depending on
 * whether the member has previously registered methods
 */
const Login = ({
  schemaURL,
  onSetAllMethods,
  onSetAvailableMethods,
  onChooseMethod,
}) => {
  const [loading, setLoading] = useState(false);
  const [verificationCompleted, setVerificationCompleted] = useState(false);
  const [schema, setSchema] = useState(null);
  const [schemaLoaded, setSchemaLoaded] = useState(false);
  const [prevSchema, setPrevSchema] = useState(null);

  useEffect(() => {
    api(schemaURL)
      .then(response => {
        if (response.status !== 200) {
          setSchemaLoaded(true);
          return Promise.reject();
        }
        return response.json();
      })
      .then(schemaData => {
        setSchema(schemaData);
        onSetAllMethods(schemaData.allMethods);
      })
      .catch(() => {});
  }, [schemaURL, onSetAllMethods]);

  useEffect(() => {
    if (!schema) {
      return;
    }

    const { availableMethods } = schema;

    if (!prevSchema) {
      onSetAvailableMethods(availableMethods);
      setPrevSchema(schema);
      return;
    }

    const { availableMethods: prevAvailableMethods } = prevSchema;

    const oldList = prevAvailableMethods.map(method => method.urlSegment).sort().toString();
    const newList = availableMethods.map(method => method.urlSegment).sort().toString();

    if (oldList !== newList) {
      onSetAvailableMethods(availableMethods);
    }

    setPrevSchema(schema);
  }, [schema, prevSchema, onSetAvailableMethods]);

  /**
   * Handle completion of login in it's entirety (all verification steps and any required
   * registration steps)
   */
  const handleCompleteLogin = () => {
    if (!schema) {
      return;
    }

    const { complete } = schema.endpoints;

    setLoading(true);
    window.location = complete;
  };

  /**
   * Handle an event indicating the Verification is complete
   */
  const handleCompleteVerify = () => {
    if (!schema) {
      return;
    }

    const { isFullyRegistered, backupMethod, registeredMethods } = schema;

    setVerificationCompleted(true);

    if (isFullyRegistered) {
      handleCompleteLogin();
      return;
    }

    if (
      registeredMethods
      && registeredMethods.length
      && registeredMethods.filter(
        method => method.urlSegment === backupMethod.urlSegment
      ).length === 0
    ) {
      onChooseMethod(backupMethod);
    }
  };

  /**
   * @return {null|Register}
   */
  const renderRegister = () => {
    if (!schema
      || !schema.endpoints
      || !schema.endpoints.register
      || (!verificationCompleted && schema.registeredMethods.length)
    ) {
      return null;
    }

    return (
      <Register
        {...schema}
        onCompleteRegistration={handleCompleteLogin}
      />
    );
  };

  /**
   * @return {null|Verify}
   */
  const renderVerify = () => {
    if (!schema || verificationCompleted || !schema.registeredMethods.length) {
      return null;
    }

    return (
      <Verify {...schema} onCompleteVerification={handleCompleteVerify} />
    );
  };

  const { ss: { i18n } } = window;

  if (!schema || loading) {
    if (!schema && schemaLoaded) {
      return (
        <LoadingError
          title={i18n._t('MFALogin.SOMETHING_WENT_WRONG', 'Something went wrong!')}
          controls={
            <button
              type="button"
              onClick={() => window.location.reload()}
              className="btn btn-outline-secondary"
            >
              {i18n._t('MFALogin.TRY_AGAIN', 'Try again')}
            </button>
          }
        />
      );
    }

    return <LoadingIndicator block />;
  }

  return (
    <>
      { renderRegister() }
      { renderVerify() }
    </>
  );
};

Login.propTypes = {
  schemaURL: PropTypes.string.isRequired,
};

const mapDispatchToProps = dispatch => ({
  onChooseMethod: method => dispatch(chooseMethod(method)),
  onSetAvailableMethods: methods => dispatch(setAvailableMethods(methods)),
  onSetAllMethods: methods => dispatch(setAllMethods(methods)),
});

export { Login as Component };

export default connect(null, mapDispatchToProps)(Login);
