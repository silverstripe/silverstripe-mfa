import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Authenticate from '../components/Authenticate';
import Register from '../components/Register';
import fetch from 'isomorphic-fetch';

class MultiFactorApp extends Component {
  constructor(props) {
    super(props);
    this.state = {
      schema: null,
      schemaLoaded: false,
    };
  }

  componentDidMount() {
    const thisComponent = this;
    const { schemaURL } = this.props;
    return fetch(schemaURL)
      .then(response => response.json())
      .then(schemaData =>
        thisComponent.setState({
          schema: schemaData
        })
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
    const { id } = this.props;
    const { schema, schemaLoaded } = this.state;

    if (!schema) {
      if (schemaLoaded) {
        throw new Error('Could not read configuration schema to load MFA interface');
      }
      // TODO: <Loading /> ?
      return null;
    }

    const { login } = schema;

    if (!login) {
      return <Register {...schema} />;
    }

    return (
      <div id={id}>
        <Authenticate {...schema} />
      </div>
    );
  }
}

MultiFactorApp.propTypes = {
  id: PropTypes.string,
  schemaURL: PropTypes.string
};

export default MultiFactorApp;
