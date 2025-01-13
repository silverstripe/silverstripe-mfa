/* global window */

import React, { useState, useEffect, useRef } from 'react';
import classnames from 'classnames';

function Verify(props) {
  const {
    error,
    graphic,
    method,
    name,
    moreOptionsControl,
    onCompleteVerification,
  } = props;
  const [value, setValue] = useState('');
  const codeInput = useRef(null);
  const i18n = window.ss.i18n;

  /**
   * Automatically set the focus to the code input field when the component is rendered
   */
  useEffect(() => {
    if (codeInput.current) {
      codeInput.current.focus();
    }
  }, [codeInput]);

  /**
   * Handle a change to the backup code input
   */
  function handleChange(event) {
    setValue(event.target.value);
  }

  /**
   * Handle pressing the "next" button after entering a backup code
   */
  function handleCompleteVerification(event) {
    event.preventDefault();
    onCompleteVerification({ code: value });
  }

  /**
   * Render the next button and any controls that are passed down from the parent
   */
  function renderControls() {
    return <ul className="mfa-action-list mfa-action-list--backup-codes">
      <li className="mfa-action-list__item">
        <button
          className="btn btn-primary"
          disabled={value.length === 0}
          onClick={handleCompleteVerification}
        >
          {i18n._t('MFABackupCodesVerify.NEXT', 'Next')}
        </button>
      </li>
      {moreOptionsControl && (
        <li className="mfa-action-list__item">
          {moreOptionsControl}
        </li>
      )}
    </ul>;
  }

  /**
   * Render a description for this input
   */
  function renderDescription() {
    return <p>
      {i18n._t(
        'MFABackupCodesVerify.DESCRIPTION',
        'Use one of the recovery codes you received'
      )}
      &nbsp;
      {method && method.supportLink &&
        <a
          href={method.supportLink}
          target="_blank"
          rel="noopener noreferrer"
        >
          {i18n._t('MFARegister.RECOVERY_HELP', 'How to use recovery codes.')}
        </a>
      }
    </p>;
  }

  /**
   * Render the input for capturing the users backup code
   */
  function renderInput() {
    const label = i18n._t('MFABackupCodesVerify.LABEL', 'Enter recovery code');
    const formGroupClasses = classnames('mfa-verify-backup-codes__input-container', {
      'has-error': !!error,
    });
    return <div className={formGroupClasses}>
      <label htmlFor="backup-code" className="control-label">
        { label }
      </label>
      <input
        className="mfa-verify-backup-codes__input text form-control"
        type="text"
        placeholder={label}
        id="backup-code"
        ref={codeInput}
        onChange={handleChange}
      />
      {error && <div className="help-block">{error}</div>}
    </div>;
  }

  // Render the component
  return <form className="mfa-verify-backup-codes__container">
    <div className="mfa-verify-backup-codes__content">
      {renderDescription()}
      {renderInput()}
    </div>
    <div className="mfa-verify-backup-codes__image-holder">
      <img className="mfa-verify-backup-codes__image" src={graphic} alt={name} />
    </div>
    {renderControls()}
  </form>;
}

export default Verify;
