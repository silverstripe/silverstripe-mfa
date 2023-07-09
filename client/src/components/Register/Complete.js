/* eslint-disable import/no-cycle */
import React from 'react';
import PropTypes from 'prop-types';
import Title from './Title';

/**
 * This component provides a registration confirmation screen to be shown once a member has
 * completed all steps that are part of the MFA registration process
 */
const Complete = ({ onComplete, showTitle, message }) => (
  <div className="mfa-register-confirmation">
    <i className="font-icon-check-mark mfa-register-confirmation__icon" />
    { showTitle && <Title className="mfa-register-confirmation__title" /> }
    <p className="mfa-register-confirmation__description">
      {message || window.ss.i18n._t(
        'MFARegister.SETUP_COMPLETE_DESCRIPTION',
        'You will be able to edit these settings later from your profile area.'
      )}
    </p>
    <button
      onClick={onComplete}
      className="mfa-register-confirmation__continue btn btn-primary"
    >
      {window.ss.i18n._t('MFARegister.SETUP_COMPLETE_CONTINUE', 'Continue')}
    </button>
  </div>
);

Complete.propTypes = {
  onComplete: PropTypes.func.isRequired,
  showTitle: PropTypes.bool,
};

Complete.defaultProps = {
  showTitle: true,
};

export default Complete;
