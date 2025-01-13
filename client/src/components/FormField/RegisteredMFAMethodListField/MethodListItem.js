import React from 'react';
import confirm from 'reactstrap-confirm';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import moment from 'moment';
import methodShape from 'types/registeredMethod';
import Remove from './MethodListItem/Remove';
import Reset from './MethodListItem/Reset';
import SetDefault from './MethodListItem/SetDefault';

import fallbacks from '../../../../lang/src/en.json';

/**
 * Renders a single Registered MFA Method for a Member
 */
function MethodListItem(props) {
  const {
    allAvailableMethods,
    backupMethod,
    canRemove,
    canReset,
    className,
    createdDate,
    endpoints,
    isBackupMethod,
    isDefaultMethod,
    method,
    RemoveComponent,
    resources,
    SetDefaultComponent,
    tag: Tag,
  } = props;
  const i18n = window.ss.i18n;

  /**
   * Get the status message template for the method item, depending on whether
   * it is the default, backup, or a regular method.
   */
  function getStatusMessage() {
    if (isDefaultMethod) {
      return i18n._t(
        'MultiFactorAuthentication.DEFAULT_REGISTERED',
        fallbacks['MultiFactorAuthentication.DEFAULT_REGISTERED']
      );
    }
    if (isBackupMethod) {
      return i18n._t(
        'MultiFactorAuthentication.BACKUP_REGISTERED',
        fallbacks['MultiFactorAuthentication.BACKUP_REGISTERED']
      );
    }
    return i18n._t(
      'MultiFactorAuthentication.REGISTERED',
      fallbacks['MultiFactorAuthentication.REGISTERED']
    );
  }

  function renderRemove() {
    if (!canRemove) {
      return null;
    }
    return <RemoveComponent
      method={method}
      backupMethod={backupMethod}
      endpoints={endpoints}
    />;
  }

  function renderReset() {
    if (!canReset) {
      return null;
    }
    const resetProps = {
      method,
      allAvailableMethods,
      backupMethod,
      endpoints,
      resources,
    };
    // Overload onReset to confirm with user for backups only
    if (isBackupMethod) {
      const confirmMessage = i18n._t(
        'MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION',
        fallbacks['MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION']
      );
      const confirmTitle = i18n._t(
        'MultiFactorAuthentication.CONFIRMATION_TITLE',
        fallbacks['MultiFactorAuthentication.CONFIRMATION_TITLE']
      );
      const buttonLabel = i18n._t(
        'MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION_BUTTON',
        fallbacks['MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION_BUTTON']
      );
      resetProps.onReset = async callback => {
        if (!await confirm({
          title: confirmTitle,
          message: confirmMessage,
          confirmText: buttonLabel
        })) {
          return;
        }
        callback();
      };
    }
    return <Reset {...resetProps} />;
  }

  /**
   * Renders a button to make the current method the default registered method
   */
  function renderSetAsDefault() {
    if (isDefaultMethod || isBackupMethod) {
      return null;
    }
    return <SetDefaultComponent method={method} endpoints={endpoints} />;
  }

  function renderControls() {
    if (!canRemove && !canReset) {
      return null;
    }
    return (
      <div>
        { renderRemove() }
        { renderReset() }
        { renderSetAsDefault() }
      </div>
    );
  }

  /**
   * Gets the method name and status, including whether it's default, backup, etc
   */
  function renderNameAndStatus() {
    const statusMessage = getStatusMessage();
    moment.locale(i18n.detectLocale());
    return i18n.inject(statusMessage, {
      method: method.name,
      date: moment(createdDate).format('L'),
    });
  }

  // Render the component
  const classes = classNames(className, 'registered-method-list-item');
  return <Tag className={classes}>
    { renderNameAndStatus() }
    { renderControls() }
  </Tag>;
}

MethodListItem.propTypes = {
  method: methodShape.isRequired,
  isDefaultMethod: PropTypes.bool,
  isBackupMethod: PropTypes.bool,
  canRemove: PropTypes.bool,
  canReset: PropTypes.bool,
  onRemove: PropTypes.func,
  onReset: PropTypes.func,
  createdDate: PropTypes.string,
  className: PropTypes.string,
  tag: PropTypes.string,
  RemoveComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
  SetDefaultComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
};

MethodListItem.defaultProps = {
  isDefaultMethod: false,
  isBackupMethod: false,
  canRemove: false,
  canReset: false,
  tag: 'li',
  RemoveComponent: Remove,
  SetDefaultComponent: SetDefault
};

export default MethodListItem;
