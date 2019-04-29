import React from 'react';

const fallbacks = require('../../../../lang/src/en.json');

const MethodListItem = ({ method, suffix = '' }) => {
  const { ss: { i18n } } = window;

  return (
    <li>
      { method.name }{ suffix.length > 0 && suffix }:&nbsp;
      <b>
        {i18n._t(
          'MultiFactorAuthentication.REGISTERED',
          fallbacks['MultiFactorAuthentication.REGISTERED']
        )}
      </b>
    </li>
  );
};

const ReadOnlyView = ({ defaultMethod, methods = [] }) => {
  const tDefault = window.ss.i18n._t(
    'MultiFactorAuthentication.DEFAULT',
    fallbacks['MultiFactorAuthentication.DEFAULT']
  );

  return (
    <div className="registered-mfa-method-list-field registered-mfa-method-list-field--read-only">
      <ul className="method-list">
        { defaultMethod && (<MethodListItem method={defaultMethod} suffix={` (${tDefault})`} />) }
        { methods.map(method => (<MethodListItem method={method} key={method.name} />)) }
      </ul>
    </div>
  );
};

export default ReadOnlyView;
