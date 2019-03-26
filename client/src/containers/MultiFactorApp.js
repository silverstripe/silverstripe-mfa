/* global window */

import React, { Component, Fragment } from 'react';
import PropTypes from 'prop-types';
import Login from 'components/Login';
import Register from 'components/Register';
import LoadingIndicator from 'components/LoadingIndicator';


class MultiFactorApp extends Component {
  constructor(props) {
    super(props);

    this.state = {
      loading: false,
      loginCompleted: false,
      schema: null,
      schemaLoaded: false,
    };

    this.handleCompleteLogin = this.handleCompleteLogin.bind(this);
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

  /**
   * Handle an event indicating the login is complete
   */
  handleCompleteLogin() {
    const { schema: { endpoints: { complete }, isFullyRegistered } } = this.state;

    // Mark login as being completed. The server side will validate any further request - this state
    // is just for controlling flow
    this.setState({
      loginCompleted: true,
    });

    // Redirect if the member is marked as having fully registered MFA
    if (isFullyRegistered) {
      this.setState({
        loading: true,
      });
      window.location = complete;
    }
  }

  /**
   * @return {null|Register}
   */
  renderRegister() {
    const { schema, loginCompleted } = this.state;

    if (!schema || (!loginCompleted && schema.registeredMethods.length)) {
      return null;
    }

    return <Register {...schema} />;
  }

  /**
   * @return {null|Login}
   */
  renderLogin() {
    const { schema, loginCompleted } = this.state;

    if (!schema || loginCompleted || !schema.registeredMethods.length) {
      return null;
    }

    return (
      <Login {...schema} onCompleteLogin={this.handleCompleteLogin} />
    );
  }

  /**
   * Directs the flow of the log in process. Two factors play into this:
   * - Schema: all information comes from a JSON schema fetched on mount {@see componentDidMount}
   * - Login: boolean - true if member is logging in (show other factors)
   *
   * If Login is false, this indicates that a member is fully authenticated. We can show the log
   * out button, and/or the ability to register for other authentication factor methods.
   *
   * flow proceeds as follows:
   * 1. no schema: error.
   * 2. schema, member, not login: register for a MFA method
   * 3. schema, member, login: show more authentication factors
   */
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
        { this.renderLogin() }
      </Fragment>
    );
  }
}

MultiFactorApp.propTypes = {
  schemaURL: PropTypes.string
};

export default MultiFactorApp;
