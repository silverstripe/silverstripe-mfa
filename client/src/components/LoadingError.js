import React from 'react';
import CircleWarning from 'components/Icons/CircleWarning';

/**
 * Renders an error screen, used for when a schema fetch fails in the
 * MFA app, or if a method is unavailable to be used but is already
 * selected in the verification process.
 *
 * @param {string} title
 * @param {string} message
 * @param {mixed} controls
 * @returns {HTMLElement}
 */
export default ({ title, message, controls }) => (
  <div className="mfa-method mfa-method--unavailable">
    <div className="mfa-method-icon mfa-method-icon--unavailable">
      <CircleWarning size="80px" />
    </div>

    <h2 className="mfa-method-title mfa-method-title--unavailable">
      {title}
    </h2>
    {message && <p>{message}</p>}

    <div className="mfa-method-options">
      {controls}
    </div>
  </div>
);
