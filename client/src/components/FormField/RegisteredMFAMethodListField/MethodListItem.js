import React, { PureComponent } from 'react';
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
 *
 * @param {object} method
 * @param {string} suffix
 * @returns {HTMLElement}
 * @constructor
 */

class MethodListItem extends PureComponent {
  /**
   * Get the status message template for the method item, depending on whether
   * it is the default, backup, or a regular method.
   *
   * @returns {string}
   */
  getStatusMessage() {
    const { isBackupMethod, isDefaultMethod } = this.props;
    const { ss: { i18n } } = window;

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

  renderRemove() {
    const { canRemove, method, RemoveComponent } = this.props;

    if (!canRemove) {
      return null;
    }

    return <RemoveComponent method={method} />;
  }

  renderReset() {
    const { canReset, isBackupMethod, method } = this.props;

    if (!canReset) {
      return null;
    }

    const props = {
      method,
    };

    // Overload onReset to confirm with user for backups only
    if (isBackupMethod) {
      const { ss: { i18n } } = window;
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

      props.onReset = async callback => {
        if (!await confirm({ title: confirmTitle, message: confirmMessage, confirmText: buttonLabel })) {
          return;
        }
        callback();
      };
    }

    return <Reset {...props} />;
  }

  /**
   * Renders a button to make the current method the default registered method
   *
   * @returns {SetDefault}
   */
  renderSetAsDefault() {
    const { isDefaultMethod, isBackupMethod, method, SetDefaultComponent } = this.props;

    if (isDefaultMethod || isBackupMethod) {
      return null;
    }

    return <SetDefaultComponent method={method} />;
  }

  renderControls() {
    const { canRemove, canReset } = this.props;

    if (!canRemove && !canReset) {
      return null;
    }

    return (
      <div>
        { this.renderRemove() }
        { this.renderReset() }
        { this.renderSetAsDefault() }
      </div>
    );
  }

  /**
   * Gets the method name and status, including whether it's default, backup, etc
   *
   * @returns {string}
   */
  renderNameAndStatus() {
    const { method, createdDate } = this.props;
    const { ss: { i18n } } = window;

    const statusMessage = this.getStatusMessage();

    moment.locale(i18n.detectLocale());

    return i18n.inject(statusMessage, {
      method: method.name,
      date: moment(createdDate).format('L'),
    });
  }

  render() {
    const { tag: Tag, className } = this.props;

    const classes = classNames(className, 'registered-method-list-item');

    return (
      <Tag className={classes}>
        { this.renderNameAndStatus() }
        { this.renderControls() }
      </Tag>
    );
  }
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
