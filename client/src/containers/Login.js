/* global window */

import React, { Component, Fragment } from 'react';
import PropTypes from 'prop-types';
import Verify from 'components/Verify';
import Register from 'components/Register';
import LoadingIndicator from 'components/LoadingIndicator';
import { Provider } from 'react-redux';
import { createStore } from 'redux';
import mfaRegisterReducer from 'state/mfaRegister/reducer';
import { chooseMethod, setAvailableMethods } from 'state/mfaRegister/actions';

const store = createStore(
  mfaRegisterReducer,
  window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__()
);

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

    this.handleCompleteVerify = this.handleCompleteVerify.bind(this);
  }

  componentDidMount() {
    const { schemaURL } = this.props;
    return fetch(schemaURL)
      .then(response => response.json())
      .then(schemaData =>
        this.setState({
          schema: schemaData
        })
      );
  }

  componentDidUpdate(prevProps, prevState) {
    // On initialisation the schema can be blank - @see componentDidMount
    if (!this.state.schema) {
      return;
    }

    const { availableMethods } = this.state.schema;

    // If the schema was previously unset then we're updating from new schema.
    if (!prevState.schema) {
      store.dispatch(setAvailableMethods(availableMethods));
      return;
    }

    // Otherwise there's some change to the schema - we need to update Redux if the available
    // methods have changed
    const { availableMethods: prevAvailableMethods } = prevState.schema;

    const oldList = prevAvailableMethods.map(method => method.urlSegment).sort().toString();
    const newList = availableMethods.map(method => method.urlSegment).sort().toString();

    if (oldList !== newList) {
      store.dispatch(setAvailableMethods(availableMethods));
    }
  }

  /**
   * Handle an event indicating the Verification is complete
   */
  handleCompleteVerify() {
    const { schema: {
      endpoints: { complete },
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
      this.setState({
        loading: true,
      });
      window.location = complete;
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
      store.dispatch(chooseMethod(backupMethod));
    }
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
      <Provider store={store}>
        <Register {...schema} />
      </Provider>
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

    if (!schema || loading) {
      if (!schema && schemaLoaded) {
        throw new Error('Could not read configuration schema to load MFA interface');
      }

      return <LoadingIndicator />;
    }

    return (
      <Fragment>
        { this.renderRegister() }
        { this.renderVerify() }
      </Fragment>
    );
  }
}

Login.propTypes = {
  schemaURL: PropTypes.string
};

export default Login;
