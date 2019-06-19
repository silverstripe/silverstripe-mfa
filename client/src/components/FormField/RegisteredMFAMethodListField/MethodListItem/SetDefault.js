import React, { Component } from 'react';
import PropTypes from 'prop-types';
import registeredMethodShape from 'types/registeredMethod';
import Config from 'lib/Config'; // eslint-disable-line

const fallbacks = require('../../../../../lang/src/en.json');

/**
 * An action to set the current method as the default registered method for a user
 */
class SetDefault extends Component {
  constructor(props) {
    super(props);

    this.handleSetDefault = this.handleSetDefault.bind(this);
  }

  handleSetDefault() {
    const { method } = this.props;
    const { endpoints: { setDefault } } = this.context;

    const token = Config.get('SecurityID');
    const endpoint = `${setDefault.replace('{urlSegment}', method.urlSegment)}?SecurityID=${token}`;

    fetch(endpoint, {
      method: 'POST',
    }).then(response => response.json().then(json => {
      if (response.status === 200) {
        // onDeregisterMethod(method);
        // onAddAvailableMethod(json.availableMethod);
        console.log('success');
        return;
      }

      const message = (json.errors && ` Errors: \n - ${json.errors.join('\n -')}`) || '';
      throw Error(`Could not delete method. Error code ${response.status}.${message}`);
    }));
  }

  render() {
    const { ss: { i18n } } = window;

    return (
      <button
        className="registered-method-list-item__control"
        type="button"
        onClick={this.handleSetDefault}
      >
        {i18n._t(
          'MultiFactorAuthentication.SET_AS_DEFAULT',
          fallbacks['MultiFactorAuthentication.SET_AS_DEFAULT']
        )}
      </button>
    );
  }
}

SetDefault.propTypes = {
  method: registeredMethodShape.isRequired,
};

SetDefault.contextTypes = {
  endpoints: PropTypes.shape({
    setDefault: PropTypes.string
  }),
};

export default SetDefault;
