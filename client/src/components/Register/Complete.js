import React from 'react';

/**
 * This component provides a registration confirmation screen to be shown once a member has
 * completed all steps that are part of the MFA registration process
 */
export default ({ onComplete }) => (
  <div className="mfa-register-confirmation">
    <i className="font-icon-check-mark mfa-register-confirmation__icon" />
    <h2 className="mfa-register-confirmation__title">
      {window.ss.i18n._t(
        'MFARegister.SETUP_COMPLETE_TITLE',
        'Multi-factor authentication is now set up'
      )}
    </h2>
    <p className="mfa-register-confirmation__description">
      {window.ss.i18n._t(
        'MFARegister.SETUP_COMPLETE_DESCRIPTION',
        'You can edit these settings from your profile area in the menu.'
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
