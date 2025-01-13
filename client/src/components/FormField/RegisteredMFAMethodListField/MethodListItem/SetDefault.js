import React from 'react';
import PropTypes from 'prop-types';
import registeredMethodShape from 'types/registeredMethod';
import Config from 'lib/Config'; // eslint-disable-line
import { connect } from 'react-redux';
import api from 'lib/api';
import { setDefaultMethod } from 'state/mfaAdministration/actions';

import fallbacks from '../../../../../lang/src/en.json';

/**
 * An action to set the current method as the default registered method for a user
 */
function SetDefault(props) {
  const {
    endpoints,
    method,
    onSetDefaultMethod,
  } = props;

  const i18n = window.ss.i18n;

  function handleSetDefault() {
    const { setDefault } = endpoints;
    const token = Config.get('SecurityID');
    const endpoint = `${setDefault.replace('{urlSegment}', method.urlSegment)}?SecurityID=${token}`;
    api(endpoint, 'PUT')
      .then(response => response.json().then(json => {
        if (response.status === 200) {
          onSetDefaultMethod(method.urlSegment);
          return;
        }
        const message = (json.errors && ` Errors: \n - ${json.errors.join('\n -')}`) || '';
        throw Error(`Could not set default method. Error code ${response.status}.${message}`);
      }));
  }

  // Render the component
  return <button
    className="registered-method-list-item__control"
    type="button"
    onClick={handleSetDefault}
  >
    {i18n._t(
      'MultiFactorAuthentication.SET_AS_DEFAULT',
      fallbacks['MultiFactorAuthentication.SET_AS_DEFAULT']
    )}
  </button>;
}

SetDefault.propTypes = {
  method: registeredMethodShape.isRequired,
};

SetDefault.contextTypes = {
  endpoints: PropTypes.shape({
    setDefault: PropTypes.string
  }),
};

const mapDispatchToProps = dispatch => ({
  onSetDefaultMethod: urlSegment => dispatch(setDefaultMethod(urlSegment)),
});

export { SetDefault as Component };

export default connect(null, mapDispatchToProps)(SetDefault);
