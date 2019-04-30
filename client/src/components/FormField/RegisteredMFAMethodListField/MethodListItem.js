import React from 'react';

const fallbacks = require('../../../../lang/src/en.json');

/**
 * Renders a single Registered MFA Method for a Member
 *
 * @todo Add actions when not in read-only mode
 * @param {object} method
 * @param {string} suffix
 * @returns {HTMLElement}
 * @constructor
 */
const MethodListItem = ({ method, suffix = '' }) => {
  const { ss: { i18n } } = window;

  return (
    <li>
      { method.name }{ suffix.length > 0 && ` ${suffix}` }:&nbsp;
      <b>
        {i18n._t(
          'MultiFactorAuthentication.REGISTERED',
          fallbacks['MultiFactorAuthentication.REGISTERED']
        )}
      </b>
    </li>
  );
};

export default MethodListItem;
