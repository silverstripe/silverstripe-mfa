/* global window */

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Login from 'components/Login';
import Register from 'components/Register';
import LoadingIndicator from 'components/LoadingIndicator';


class MultiFactorApp extends Component {
  constructor(props) {
    const { ss: { i18n } } = window;

    super(props);
    this.state = {
      loginCompleted: false,
      schema: null,
      schemaLoaded: false,
      title: i18n._t('MFA.TITLE', 'Multi-factor authentication'),
    };

    this.handleSetTitle = this.handleSetTitle.bind(this);
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
   * Handle a request to change the title of the page
   *
   * @param {string} title
   */
  handleSetTitle(title) {
    this.setState({
      title,
    });
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
      window.location = complete;
    }
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
    const { schema, schemaLoaded, loginCompleted, title } = this.state;

    if (!schema) {
      if (schemaLoaded) {
        throw new Error('Could not read configuration schema to load MFA interface');
      }

      return <LoadingIndicator />;
    }

    const { registeredMethods } = schema;
    const showRegister = loginCompleted || !registeredMethods.length;

    return (
      <div>
        {title && <h1>{title}</h1>}
        {showRegister && <Register {...schema} onSetTitle={this.handleSetTitle} />}
        {showRegister || (
          <Login
            {...schema}
            onCompleteLogin={this.handleCompleteLogin}
            onSetTitle={this.handleSetTitle}
          />
        )}
      </div>
    );
  }
}

MultiFactorApp.propTypes = {
  schemaURL: PropTypes.string
};

export default MultiFactorApp;
