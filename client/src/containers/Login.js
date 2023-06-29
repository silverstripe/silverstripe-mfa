/* global window */

import React, { Component, Fragment } from 'react';
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
class Login extends Component {
  constructor(props) {
    super(props);

    this.state = {
      loading: false,
      verificationCompleted: false,
      schema: null,
      schemaLoaded: false,
    };

    this.handleCompleteLogin = this.handleCompleteLogin.bind(this);
    this.handleCompleteVerify = this.handleCompleteVerify.bind(this);
  }

  componentDidMount() {
    const { schemaURL, onSetAllMethods } = this.props;

    return api(schemaURL)
      .then(response => {
        if (response.status !== 200) {
          this.setState({
            schemaLoaded: true, // Triggers an error state - see render()
          });
          return Promise.reject();
        }
        return response.json();
      })
      .then(schemaData => {
        this.setState({
          schema: schemaData
        });
        onSetAllMethods(schemaData.allMethods);
      })
      .catch(() => {}); // noop
  }

  componentDidUpdate(prevProps, prevState) {
    // On initialisation the schema can be blank - @see componentDidMount
    if (!this.state.schema) {
      return;
    }

    const { availableMethods } = this.state.schema;

    // If the schema was previously unset then we're updating from new schema.
    if (!prevState.schema) {
      this.props.onSetAvailableMethods(availableMethods);
      return;
    }

    // Otherwise there's some change to the schema - we need to update Redux if the available
    // methods have changed
    const { availableMethods: prevAvailableMethods } = prevState.schema;

    const oldList = prevAvailableMethods.map(method => method.urlSegment).sort().toString();
    const newList = availableMethods.map(method => method.urlSegment).sort().toString();

    if (oldList !== newList) {
      this.props.onSetAvailableMethods(availableMethods);
    }
  }

  /**
   * Handle an event indicating the Verification is complete
   */
  handleCompleteVerify() {
    const { schema: {
      isFullyRegistered,
      backupMethod,
      registeredMethods,
    } } = this.state;

    // Mark verification as being completed. The server side will validate any further request -
    // this state is just for controlling flow
    this.setState({
      verificationCompleted: true,
    });

    // Redirect if the member is marked as having fully registered MFA
    if (isFullyRegistered) {
      this.handleCompleteLogin();
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
      this.props.onChooseMethod(backupMethod);
    }
  }

  /**
   * Handle completion of login in it's entirety (all verification steps and any required
   * registration steps)
   */
  handleCompleteLogin() {
    const { complete } = this.state.schema.endpoints;

    this.setState({
      loading: true,
    });
    window.location = complete;
  }

  /**
   * @return {null|Register}
   */
  renderRegister() {
    const { schema, verificationCompleted } = this.state;

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
        onCompleteRegistration={this.handleCompleteLogin}
      />
    );
  }

  /**
   * @return {null|Verify}
   */
  renderVerify() {
    const { schema, verificationCompleted } = this.state;

    if (!schema || verificationCompleted || !schema.registeredMethods.length) {
      return null;
    }

    return (
      <Verify {...schema} onCompleteVerification={this.handleCompleteVerify} />
    );
  }

  render() {
    const { schema, schemaLoaded, loading } = this.state;
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
        { this.renderRegister() }
        { this.renderVerify() }
      </>
    );
  }
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
