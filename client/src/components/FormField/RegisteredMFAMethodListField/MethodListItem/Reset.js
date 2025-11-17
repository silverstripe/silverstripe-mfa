import React, { useState, useContext } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import {
  SCREEN_CHOOSE_METHOD,
  SCREEN_INTRODUCTION,
  SCREEN_REGISTER_METHOD,
} from 'components/Register';
import { chooseMethod, showScreen } from 'state/mfaRegister/actions';
import registeredMethodShape from 'types/registeredMethod';
import RegisterModal from 'components/RegisterModal';

import fallbacks from '../../../../../lang/src/en.json';
import { MFAMethodListContext } from '../MFAMethodListContext';

const Reset = ({
  method,
  onReset,
  onResetMethod,
}) => {
  const [modalOpen, setModalOpen] = useState(false);
  const { allAvailableMethods, backupMethod, endpoints, resources } = useContext(MFAMethodListContext);

  const handleToggleModal = () => {
    setModalOpen(state => !state);
  };

  const handleReset = () => {
    const availableMethodDetail = allAvailableMethods.find(
      candidate => candidate.urlSegment === method.urlSegment
    );

    if (!availableMethodDetail) {
      throw Error(`Cannot register the method given: ${method.name} (${method.urlSegment}).`);
    }

    onResetMethod(availableMethodDetail);
    handleToggleModal();
  };

  const callback = onReset ? () => onReset(handleReset) : handleReset;

  return (
    <button
      className="registered-method-list-item__control"
      type="button"
      onClick={callback}
    >
      {window.ss.i18n._t(
        'MultiFactorAuthentication.RESET_METHOD',
        fallbacks['MultiFactorAuthentication.RESET_METHOD']
      )}
      <RegisterModal
        backupMethod={backupMethod}
        isOpen={modalOpen}
        toggle={handleToggleModal}
        resources={resources}
        endpoints={endpoints}
        disallowedScreens={[SCREEN_CHOOSE_METHOD, SCREEN_INTRODUCTION]}
      />
    </button>
  );
};

Reset.propTypes = {
  method: registeredMethodShape.isRequired,
  onReset: PropTypes.func,
  onResetMethod: PropTypes.func.isRequired,
};

export default connect(null, dispatch => ({ onResetMethod: method => {
  dispatch(chooseMethod(method));
  dispatch(showScreen(SCREEN_REGISTER_METHOD));
} }))(Reset);
