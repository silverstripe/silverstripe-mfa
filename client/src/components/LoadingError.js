import React from 'react';
import PropTypes from 'prop-types';
import CircleWarning from 'components/Icons/CircleWarning';

/**
 * Renders an error screen, used for when a schema fetch fails in the
 * MFA app, or if a method is unavailable to be used but is already
 * selected in the verification process.
 */
const LoadingError = ({ title, message, controls }) => (
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

LoadingError.propTypes = {
  title: PropTypes.string.isRequired,
  message: PropTypes.string,
  controls: PropTypes.oneOfType([PropTypes.node, PropTypes.func]),
};

export default LoadingError;
