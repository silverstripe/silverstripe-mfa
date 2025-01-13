import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import confirm from 'reactstrap-confirm';
import Config from 'lib/Config'; // eslint-disable-line
import api from 'lib/api';
import { addAvailableMethod } from 'state/mfaRegister/actions';
import { deregisterMethod, setDefaultMethod } from 'state/mfaAdministration/actions';
import registeredMethodShape from 'types/registeredMethod';

import fallbacks from '../../../../../lang/src/en.json';

const Remove = (props) => {
  const {
    backupMethod,
    endpoints,
    defaultMethod,
    method,
    onRemove,
    registeredMethods,
    onDeregisterMethod,
    onAddAvailableMethod,
    onSetDefaultMethod,
  } = props;
  const remove = endpoints.remove;
  const i18n = window.ss.i18n;

  const handleRemove = async () => {
    // Confirm with the user
    const confirmMessage = i18n._t(
      'MultiFactorAuthentication.DELETE_CONFIRMATION',
      fallbacks['MultiFactorAuthentication.DELETE_CONFIRMATION']
    );
    const confirmTitle = i18n._t(
      'MultiFactorAuthentication.CONFIRMATION_TITLE',
      fallbacks['MultiFactorAuthentication.CONFIRMATION_TITLE']
    );
    const buttonLabel = i18n._t(
      'MultiFactorAuthentication.DELETE_CONFIRMATION_BUTTON',
      fallbacks['MultiFactorAuthentication.DELETE_CONFIRMATION_BUTTON']
    );

    if (!await confirm({ title: confirmTitle, message: confirmMessage, confirmText: buttonLabel })) {
      return;
    }

    const token = Config.get('SecurityID');
    const endpoint = `${remove.replace('{urlSegment}', method.urlSegment)}?SecurityID=${token}`;

    api(endpoint, 'DELETE')
      .then(response => response.json().then(json => {
        if (response.status === 200) {
          onDeregisterMethod(method);
          onAddAvailableMethod(json.availableMethod);

          if (method.urlSegment === defaultMethod) {
            onSetDefaultMethod(null);
          }

          // If the response indicates there's no backup code registered then we
          // need to ensure that the backup code isn't contained in our registered
          // methods. If it is we should remove it.
          if (!json.hasBackupMethod && backupMethod && registeredMethods.find(
            candidate => candidate.urlSegment === backupMethod.urlSegment
          )) {
            onDeregisterMethod(backupMethod);
          }

          return;
        }

        const message = (json.errors && ` Errors: \n - ${json.errors.join('\n -')}`) || '';
        throw Error(`Could not delete method. Error code ${response.status}.${message}`);
      }));
  };

  return (
    <button
      className="registered-method-list-item__control"
      type="button"
      onClick={onRemove ? onRemove(handleRemove) : handleRemove}
    >
      {i18n._t(
        'MultiFactorAuthentication.REMOVE_METHOD',
        fallbacks['MultiFactorAuthentication.REMOVE_METHOD']
      )}
    </button>
  );
};

Remove.propTypes = {
  method: registeredMethodShape.isRequired,
  onRemove: PropTypes.func,

  // Redux props:
  defaultMethod: PropTypes.string.isRequired,
  registeredMethods: PropTypes.arrayOf(registeredMethodShape).isRequired,
  onDeregisterMethod: PropTypes.func.isRequired,
  onAddAvailableMethod: PropTypes.func.isRequired,
  onSetDefaultMethod: PropTypes.func.isRequired,
};

export default connect(({ mfaAdministration: { defaultMethod, registeredMethods } }) => ({
  defaultMethod,
  registeredMethods,
}), dispatch => ({
  onDeregisterMethod: method => { dispatch(deregisterMethod(method)); },
  onAddAvailableMethod: method => { dispatch(addAvailableMethod(method)); },
  onSetDefaultMethod: urlSegment => dispatch(setDefaultMethod(urlSegment)),
}))(Remove);
