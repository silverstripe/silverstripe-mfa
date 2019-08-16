/* global window */

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import availableMethodType from 'types/availableMethod';
import withMethodAvailability from 'state/methodAvailability/withMethodAvailability';

/**
 * A centralised wrapper component to render a single MFA method tile for use
 * during the MFA registration process.
 */
class MethodTile extends Component {
  constructor(props) {
    super(props);

    this.handleClick = this.handleClick.bind(this);
    this.handleKeyUp = this.handleKeyUp.bind(this);
  }

  /**
   * If the method is enabled, trigger the click handler prop
   *
   * @param {Event} event
   */
  handleClick(event) {
    const { method: { isAvailable }, onClick } = this.props;

    if (isAvailable && onClick) {
      onClick(event);
    }
  }

  /**
   * If pressing enter key, trigger click event to load detail view
   *
   * @param {Event} event
   */
  handleKeyUp(event) {
    if (event.keyCode === 13) {
      this.handleClick(event);
    }
  }

  /**
   * Get the support link as a "target=_blank" anchor tag from the given method (if one is set)
   *
   * @param {availableMethodType} method
   * @return {HTMLElement|null}
   */
  renderSupportLink(method) {
    const { ss: { i18n } } = window;
    const { supportLink, supportText } = method;

    if (!supportLink) {
      return null;
    }

    return (
      <a
        href={supportLink}
        target="_blank"
        rel="noopener noreferrer"
        className="mfa-method-tile__support-link"
      >
        {supportText || i18n._t('MFARegister.HELP', 'Find out more.')}
      </a>
    );
  }

  /**
   * If the verification method is not available for some reason (e.g. browser support), this
   * will render a message over the top of the method tile to indicate that it's unsupported.
   *
   * @returns {HTMLElement|null}
   */
  renderUnavailableMask() {
    const { ss: { i18n } } = window;
    const { isAvailable, getUnavailableMessage } = this.props;

    if (isAvailable()) {
      return null;
    }
    const message = getUnavailableMessage();

    return (
      <div className="mfa-method-tile__unavailable-mask">
        <h3 className="mfa-method-tile__unavailable-title">
          {i18n._t('MFAMethodTile.UNAVAILABLE', 'Unsupported: ')}
        </h3>
        {message && (
          <p className="mfa-method-tile__unavailable-text">
            {message}
          </p>
        )}
      </div>
    );
  }

  render() {
    const { isActive, method } = this.props;
    const { ss: { i18n } } = window;

    const classes = classnames('mfa-method-tile', {
      'mfa-method-tile--active': isActive,
      'mfa-method-tile--unsupported': !method.isAvailable,
    });

    const thumbnailClasses = classnames('mfa-method-tile__thumbnail-container', {
      'mfa-method-tile__thumbnail-container--unsupported': !method.isAvailable,
    });

    const leadInLabel = i18n.inject(i18n._t('MFARegister.REGISTER_WITH', 'Register with {method}'), {
      method: method.name.toLowerCase(),
    });

    return (
      <li className={classes}>
        <div
          className="mfa-method-tile__content"
          onClick={this.handleClick}
          onKeyUp={this.handleKeyUp}
          tabIndex="0"
          role="button"
        >
          {method.thumbnail && (
            <div className={thumbnailClasses}>
              <img src={method.thumbnail} className="mfa-method-tile__thumbnail" alt={method.name} />
            </div>
          )}
          <h3 className="mfa-method-tile__title">{leadInLabel}</h3>
          <p className="mfa-method-tile__description">
            {method.description && `${method.description}. `}
            {this.renderSupportLink(method)}
          </p>
        </div>
        {this.renderUnavailableMask()}
      </li>
    );
  }
}

MethodTile.propTypes = {
  getUnavailableMessage: PropTypes.func.isRequired,
  isActive: PropTypes.bool,
  isAvailable: PropTypes.func.isRequired,
  method: availableMethodType.isRequired,
  onClick: PropTypes.func.isRequired,
};

MethodTile.defaultProps = {
  isActive: false,
};

export { MethodTile as Component };

export default withMethodAvailability(MethodTile);
