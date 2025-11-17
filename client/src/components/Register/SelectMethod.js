/* eslint-disable import/no-cycle */
/* global window */

import React, { useState, useEffect } from 'react';
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
const SelectMethod = ({
  methods = [],
  onSelectMethod = () => null,
  onClickBack = () => null,
  showTitle = true,
  TitleComponent = Title,
  MethodTileComponent = MethodTile,
  isAvailable,
}) => {
  // If only one method is available, automatically select it
  let initialHighlightedMethod = null;
  if (methods.length === 1 && isAvailable && isAvailable(methods[0])) {
    initialHighlightedMethod = methods[0];
  }

  const [highlightedMethod, setHighlightedMethod] = useState(initialHighlightedMethod);

  /**
   * Sets the current highlighted method as the selected method, which causes the steps to re-render
   * and proceed to the "next" screen.
   */
  const handleGoToNext = () => {
    onSelectMethod(highlightedMethod);
  };

  /**
   * Handle clicking on a method
   *
   * @param {object} method
   */
  const handleClick = (method) => {
    setHighlightedMethod(method);
  };

  /**
   * Send the user back to the introduction screen
   */
  const handleBack = () => {
    if (onClickBack) {
      onClickBack();
    }
  };

  const renderActions = () => {
    const { ss: { i18n } } = window;

    return (
      <ul className="mfa-action-list">
        <li className="mfa-action-list__item">
          <button
            className="btn btn-primary"
            disabled={highlightedMethod === null}
            onClick={handleGoToNext}
          >
            {i18n._t('MFARegister.NEXT', 'Next')}
          </button>
        </li>

        <li className="mfa-action-list__item">
          <button
            className="btn btn-secondary"
            onClick={handleBack}
          >
            {i18n._t('MFARegister.BACK', 'Back')}
          </button>
        </li>
      </ul>
    );
  };

  /**
   * If only one method is available, automatically select it
   */
  useEffect(() => {
    if (highlightedMethod) {
      handleGoToNext();
    }
  }, []);

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
            onClick={() => handleClick(method)}
          />
        ))}
      </ul>

      {renderActions()}
    </div>
  );
};

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
