/* global window */

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import MethodTile from './MethodTile';
import availableMethodType from 'types/availableMethod';
import classnames from 'classnames';

/**
 * Renders a list of authentication methods as MethodTile components
 */
class SelectMethod extends Component {
  constructor(props) {
    super(props);

    this.state = {
      highlightedMethod: null,
    };

    this.handleGoToNext = this.handleGoToNext.bind(this);
  }

  /**
   * Sets the current highlighted method as the selected method, which causes the steps to re-render
   * and proceed to the "next" screen.
   */
  handleGoToNext() {
    const { highlightedMethod } = this.state;

    this.props.onSelectMethod(highlightedMethod);
  }

  /**
   * Handle clicking on a method
   *
   * @param {object} method
   */
  handleClick(method) {
    this.setState({
      highlightedMethod: method,
    });
  }

  /**
   * Send the user back to the introduction screen
   */
  handleBack() {
    window.alert('Todo: introduction screen here');
  }

  renderActions() {
    const { ss: { i18n } } = window;
    const { highlightedMethod } = this.state;

    return (
      <div className="mfa-method-tile__actions">
        <button
          className="mfa-method-tile__action-next btn btn-success"
          disabled={highlightedMethod === null}
          onClick={this.handleGoToNext}
        >
          {i18n._t('MFARegister.NEXT', 'Next')}
        </button>

        <button
          className="mfa-method-tile__action-back btn"
          onClick={this.handleBack}
        >
          {i18n._t('MFARegister.BACK', 'Back')}
        </button>
      </div>
    );
  }

  render() {
    const { ss: { i18n } } = window;
    const { methods } = this.props;
    const { highlightedMethod } = this.state;

    const classes = classnames('mfa-method-tile__container', {
      'mfa-method-tile__container--two-columns': methods.length % 2 === 0,
    });

    return (
      <div>
        <h2>{i18n._t('MFASelectMethod.SELECT_METHOD', 'Select a verification method')}</h2>

        <ul className={classes}>
          {methods.map(method => (
            <MethodTile
              isActive={highlightedMethod === method}
              key={method.urlSegment}
              method={method}
              onClick={() => this.handleClick(method)}
            />
          ))}
        </ul>

        {this.renderActions()}
      </div>
    );
  }
}

SelectMethod.propTypes = {
  methods: PropTypes.arrayOf(
    availableMethodType,
  ),
  onSelectMethod: PropTypes.func,
};

export default SelectMethod;
