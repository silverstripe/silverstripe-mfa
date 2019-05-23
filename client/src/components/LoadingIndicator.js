import React from 'react';
import classnames from 'classnames';

export default ({ block = false, color = 'inherit', size = '6em' }) => (
  <div
    style={{ color, height: size, width: size }}
    className={classnames({ 'mfa-loading-indicator': true, 'mfa-loading-indicator--block': block })}
  />
);
