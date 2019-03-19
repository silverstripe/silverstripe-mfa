/* global window */

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Login from 'components/Login';
import Register from 'components/Register';
import LoadingIndicator from 'components/LoadingIndicator';


class MultiFactorApp extends Component {
  constructor(props) {
    super(props);
    this.state = {
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

  handleCompleteLogin() {
    const { schema: { endpoints: { complete } } } = this.state;

    window.location = complete;
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
    const { schema, schemaLoaded } = this.state;

    if (!schema) {
      if (schemaLoaded) {
        throw new Error('Could not read configuration schema to load MFA interface');
      }

      return <LoadingIndicator />;
    }

    const { registeredMethods } = schema;

    if (!registeredMethods.length) {
      return <Register {...schema} />;
    }

    return <Login {...schema} onCompleteLogin={this.handleCompleteLogin} />;
  }
}

MultiFactorApp.propTypes = {
  schemaURL: PropTypes.string
};

export default MultiFactorApp;
