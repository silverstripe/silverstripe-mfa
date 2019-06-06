import React, { PureComponent } from 'react';
import confirm from '@silverstripe/reactstrap-confirm';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import moment from 'moment';
import Remove from './MethodListItem/Remove';
import Reset from './MethodListItem/Reset';
import methodShape from 'types/registeredMethod';

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

class MethodListItem extends PureComponent {
  getNameSuffix() {
    const { ss: { i18n } } = window;
    const { isDefaultMethod } = this.props;

    let suffix = '';
    if (isDefaultMethod) {
      suffix = i18n._t(
        'MultiFactorAuthentication.DEFAULT',
        fallbacks['MultiFactorAuthentication.DEFAULT']
      );
    }
    if (suffix.length) {
      suffix = ` ${suffix}`;
    }

    return suffix;
  }

  renderRemove() {
    const { canRemove, method } = this.props;

    if (!canRemove) {
      return null;
    }

    return <Remove method={method} />;
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
        if (!await confirm(confirmMessage, { title: confirmTitle, confirmLabel: buttonLabel })) {
          return;
        }
        callback();
      };
    }

    return <Reset {...props} />;
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
      </div>
    );
  }

  renderNameAndStatus() {
    const { method, isBackupMethod, createdDate } = this.props;
    const { ss: { i18n } } = window;

    let statusMessage = i18n._t(
      'MultiFactorAuthentication.REGISTERED',
      fallbacks['MultiFactorAuthentication.REGISTERED']
    );

    if (isBackupMethod) {
      statusMessage = i18n._t(
        'MultiFactorAuthentication.BACKUP_CREATED',
        fallbacks['MultiFactorAuthentication.BACKUP_REGISTERED']
      );
    }

    moment.locale(i18n.detectLocale());

    return i18n.inject(statusMessage, {
      method: `${method.name}${this.getNameSuffix()}`,
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
};

MethodListItem.defaultProps = {
  isDefaultMethod: false,
  isBackupMethod: false,
  canRemove: false,
  canReset: false,
  tag: 'li',
};

export default MethodListItem;
