import React, { PureComponent } from 'react';
import methodShape from 'types/registeredMethod';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import moment from 'moment';

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

  renderControls() {
    const { isReadOnly, method, onResetMethod, onRemoveMethod } = this.props;
    if (isReadOnly) {
      return '';
    }

    const controls = [];
    const { ss: { i18n } } = window;

    const buildControl = (action, text) => ((
      <button
        className="registered-method-list-item__control"
        key={text}
        onClick={event => {
          event.preventDefault();
          action(method);
        }}
      >
        {text}
      </button>
    ));

    if (onResetMethod) {
      controls.push(buildControl(onResetMethod, i18n._t(
        'MultiFactorAuthentication.RESET_METHOD',
        fallbacks['MultiFactorAuthentication.RESET_METHOD']
      )));
    }

    if (onRemoveMethod) {
      controls.push(buildControl(onRemoveMethod, i18n._t(
        'MultiFactorAuthentication.REMOVE_METHOD',
        fallbacks['MultiFactorAuthentication.REMOVE_METHOD']
      )));
    }

    return <div>{ controls }</div>;
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
  isReadOnly: PropTypes.bool.isRequired,
  isDefaultMethod: PropTypes.bool,
  isBackupMethod: PropTypes.bool,
  onRemoveMethod: PropTypes.func,
  onResetMethod: PropTypes.func,
  className: PropTypes.string,
  tag: PropTypes.string
};

MethodListItem.defaultProps = {
  isDefaultMethod: false,
  isBackupMethod: false,
  tag: 'li',
};

export default MethodListItem;
