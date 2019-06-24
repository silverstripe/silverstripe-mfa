import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import confirm from '@silverstripe/reactstrap-confirm';
import Config from 'lib/Config'; // eslint-disable-line
import { addAvailableMethod } from 'state/mfaRegister/actions';
import { deregisterMethod } from 'state/mfaAdministration/actions';
import registeredMethodShape from 'types/registeredMethod';

const fallbacks = require('../../../../../lang/src/en.json');

const Remove = ({
  method,
  onRemove,

  registeredMethods,
  onDeregisterMethod,
  onAddAvailableMethod,
}, { backupMethod, endpoints: { remove } }) => {
  const { ss: { i18n } } = window;

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

    if (!await confirm(confirmMessage, { title: confirmTitle, confirmLabel: buttonLabel })) {
      return;
    }

    const token = Config.get('SecurityID');
    const endpoint = `${remove.replace('{urlSegment}', method.urlSegment)}?SecurityID=${token}`;

    fetch(endpoint, {
      method: 'DELETE',
    }).then(response => response.json().then(json => {
      if (response.status === 200) {
        onDeregisterMethod(method);
        onAddAvailableMethod(json.availableMethod);

        // If the response indicates there's no backup code registered then we need to ensure that
        // the backup code isn't contained in our registered methods. If it is we should remove it.
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
  registeredMethods: PropTypes.arrayOf(registeredMethodShape).isRequired,
  onDeregisterMethod: PropTypes.func.isRequired,
  onAddAvailableMethod: PropTypes.func.isRequired,
};

Remove.contextTypes = {
  backupMethod: registeredMethodShape,
  endpoints: PropTypes.shape({
    register: PropTypes.string,
    remove: PropTypes.string,
  }),
};

export default connect(({ mfaAdministration: { registeredMethods } }) => ({
  registeredMethods,
}), dispatch => ({
  onDeregisterMethod: method => { dispatch(deregisterMethod(method)); },
  onAddAvailableMethod: method => { dispatch(addAvailableMethod(method)); },
}))(Remove);
