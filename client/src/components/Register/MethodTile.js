/* global window */

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import availableMethodType from 'types/availableMethod';

class MethodTile extends Component {
  constructor(props) {
    super(props);

    this.handleClick = this.handleClick.bind(this);
    this.handleKeyUp = this.handleKeyUp.bind(this);
  }

  /**
   * If the method is enabled, trigger the click handler prop
   *
   * @param event
   */
  handleClick(event) {
    const { isAvailable, onClick } = this.props;

    if (isAvailable && onClick) {
      onClick(event);
    }
  }

  /**
   * If pressing enter key, trigger click event to load detail view
   *
   * @param {Object} event
   */
  handleKeyUp(event) {
    if (event.keyCode === 13) {
      this.handleClick(event);
    }
  }

  /**
   * Get the support link as a "target=_blank" anchor tag from the given method (if one is set)
   *
   * @param {object} method
   * @return {HTMLElement|null}
   */
  renderSupportLink(method) {
    const { ss: { i18n } } = window;

    if (!method.supportLink) {
      return null;
    }

    return (
      <a
        href={method.supportLink}
        target="_blank"
        rel="noopener noreferrer"
        className="mfa-method-tile__support-link"
      >
        {i18n._t('MFARegister.HELP', 'Find out more.')}
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
    const { isAvailable, unavailableMessage } = this.props;
    const { ss: { i18n } } = window;

    if (isAvailable) {
      return null;
    }

    return (
      <div className="mfa-method-tile__unavailable-mask">
        <h3 className="mfa-method-tile__unavailable-title">
          {i18n._t('MFAMethodTile.UNAVAILABLE', 'Unsupported')}
        </h3>
        {unavailableMessage && (
          <p className="mfa-method-tile__unavailable-text">
            {unavailableMessage}
          </p>
        )}
      </div>
    );
  }

  render() {
    const { isActive, method } = this.props;

    const classes = classnames('mfa-method-tile', {
      'mfa-method-tile--active': isActive,
    });

    return (
      <li className={classes}>
        {this.renderUnavailableMask()}
        <div
          className="mfa-method-tile__content"
          onClick={this.handleClick}
          onKeyUp={this.handleKeyUp}
          tabIndex="0"
          role="button"
        >
          {method.thumbnail && (
            <div className="mfa-method-tile__thumbnail-container">
              <img src={method.thumbnail} className="mfa-method-tile__thumbnail" alt={method.name} />
            </div>
          )}
          <h3 className="mfa-method-tile__title">{method.name}</h3>
          <p className="mfa-method-tile__description">
            {method.description}. {this.renderSupportLink(method)}
          </p>
        </div>
      </li>
    );
  }
}

MethodTile.propTypes = {
  isActive: PropTypes.bool,
  isAvailable: PropTypes.bool,
  unavailableMessage: PropTypes.string,
  method: availableMethodType.isRequired,
  onClick: PropTypes.func.isRequired,
};

MethodTile.defaultProps = {
  isActive: false,
  isAvailable: true,
  unavailableMessage: '',
};

export default MethodTile;
