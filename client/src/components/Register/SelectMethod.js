/* eslint-disable import/no-cycle */
/* global window */

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import availableMethodType from 'types/availableMethod';
import classnames from 'classnames';
import { showScreen, chooseMethod } from 'state/mfaRegister/actions';
import { compose } from 'redux';
import { connect } from 'react-redux';
import withMethodAvailability from 'state/methodAvailability/withMethodAvailability';
import { SCREEN_INTRODUCTION, SCREEN_REGISTER_METHOD } from '../Register';
import MethodTile from './MethodTile';
import Title from './Title';

/**
 * Renders a list of authentication methods as MethodTile components
 */
class SelectMethod extends Component {
  constructor(props) {
    super(props);

    // If only one method is available, automatically select it
    let highlightedMethod = null;
    if (props.methods.length === 1 && props.isAvailable && props.isAvailable(props.methods[0])) {
      highlightedMethod = props.methods[0];
    }

    this.state = {
      highlightedMethod,
    };

    this.handleGoToNext = this.handleGoToNext.bind(this);
    this.handleBack = this.handleBack.bind(this);
  }

  /**
   * If only one method is available, automatically select it
   */
  componentDidMount() {
    const { highlightedMethod } = this.state;

    if (highlightedMethod) {
      this.handleGoToNext();
    }
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
    if (this.props.onClickBack) {
      this.props.onClickBack();
    }
  }

  renderActions() {
    const { ss: { i18n } } = window;
    const { highlightedMethod } = this.state;

    return (
      <ul className="mfa-action-list">
        <li className="mfa-action-list__item">
          <button
            className="btn btn-primary"
            disabled={highlightedMethod === null}
            onClick={this.handleGoToNext}
          >
            {i18n._t('MFARegister.NEXT', 'Next')}
          </button>
        </li>

        <li className="mfa-action-list__item">
          <button
            className="btn btn-secondary"
            onClick={this.handleBack}
          >
            {i18n._t('MFARegister.BACK', 'Back')}
          </button>
        </li>
      </ul>
    );
  }

  render() {
    const { methods, showTitle, TitleComponent, MethodTileComponent } = this.props;
    const { highlightedMethod } = this.state;

    const classes = classnames('mfa-method-tile-group', {
      'mfa-method-tile-group--three-columns': methods.length % 3 === 0,
    });

    return (
      <div>
        {showTitle && <TitleComponent />}

        <ul className={classes}>
          {methods.map(method => (
            <MethodTileComponent
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
  onClickBack: PropTypes.func,
  showTitle: PropTypes.bool,
  TitleComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
  MethodTileComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
};

SelectMethod.defaultProps = {
  showTitle: true,
  TitleComponent: Title,
  MethodTileComponent: MethodTile
};

const mapDispatchToProps = dispatch => ({
  onClickBack: () => dispatch(showScreen(SCREEN_INTRODUCTION)),
  onSelectMethod: (method) => {
    dispatch(chooseMethod(method));
    dispatch(showScreen(SCREEN_REGISTER_METHOD));
  }
});

export { SelectMethod as Component };

export default compose(
  connect(null, mapDispatchToProps),
  withMethodAvailability
)(SelectMethod);
