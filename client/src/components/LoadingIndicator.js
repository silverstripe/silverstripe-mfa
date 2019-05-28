import React from 'react';
import classnames from 'classnames';

export default ({ block = false, size = '6em' }) => (
  <div
    style={{ height: size, width: size }}
    className={classnames({ 'mfa-loading-indicator': true, 'mfa-loading-indicator--block': block })}
  />
);
