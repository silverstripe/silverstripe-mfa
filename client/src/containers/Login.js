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
 * All information comes from a JSON schema fetched on mount {@see componentDidMount}
 *
 * This component will either render a verification screen or a registration screen depending on
 * whether the member has previously registered methods
 */
function Login(props) {
  const {
    schemaURL,
    onChooseMethod,
    onSetAllMethods,
    onSetAvailableMethods,
  } = props;

  const [loading, setLoading] = useState(false);
  const [verificationCompleted, setVerificationCompleted] = useState(false);
  const [schema, setSchema] = useState(null);
  const [schemaLoaded, setSchemaLoaded] = useState(false);

  const availableMethods = schema ? schema.availableMethods : null;
  const i18n = window.ss.i18n;

  useEffect(() => {
    async function fetchData() {
      await api(schemaURL)
        .then(response => {
          if (response.status !== 200) {
            // Triggers an error state - see render
            setSchemaLoaded(true);
            return Promise.reject();
          }
          return response.json();
        })
        .then(schemaData => {
          setSchema(schemaData);
          onSetAllMethods(schemaData.allMethods);
        })
        .catch(() => {}); // noop
    }
    fetchData();
  }, []);

  useEffect(() => {
    if (!availableMethods) {
      return;
    }
    onSetAvailableMethods(availableMethods);
  }, [availableMethods]);

  /**
   * Handle completion of login in it's entirety (all verification steps and any required
   * registration steps)
   */
  function handleCompleteLogin() {
    setLoading(true);
    window.location = schema.endpoints.complete;
  }

  /**
   * Handle an event indicating the Verification is complete
   */
  function handleCompleteVerify() {
    const { isFullyRegistered, backupMethod, registeredMethods } = schema;
    // Mark verification as being completed. The server side will validate any further request -
    // this state is just for controlling flow
    setVerificationCompleted(true);
    // Redirect if the member is marked as having fully registered MFA
    if (isFullyRegistered) {
      handleCompleteLogin();
      return;
    }
    // Check if the backup method should be chosen for the register screen and update redux
    if (
      registeredMethods
      && registeredMethods.length
      && registeredMethods.filter(
        method => method.urlSegment === backupMethod.urlSegment
      ).length === 0
    ) {
      onChooseMethod(backupMethod);
    }
  }

  function renderRegister() {
    if (!schema
      || !schema.endpoints
      || !schema.endpoints.register
      || (!verificationCompleted && schema.registeredMethods.length)
    ) {
      return null;
    }
    return <Register {...schema} onCompleteRegistration={() => handleCompleteLogin()} />;
  }

  function renderVerify() {
    if (!schema || verificationCompleted || !schema.registeredMethods.length) {
      return null;
    }
    return <Verify {...schema} onCompleteVerification={() => handleCompleteVerify()} />;
  }

  // Render component
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
  return <>
    { renderRegister() }
    { renderVerify() }
  </>;
}

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
