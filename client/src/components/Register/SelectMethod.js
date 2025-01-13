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
function SelectMethod(props) {
  const {
    methods,
    MethodTileComponent,
    onClickBack,
    onSelectMethod,
    showTitle,
    TitleComponent,
  } = props;
  const i18n = window.ss.i18n;

  // If only one method is available, automatically select it
  let initialHighlightedMethod = null;
  if (props.methods.length === 1 && props.isAvailable && props.isAvailable(props.methods[0])) {
    initialHighlightedMethod = props.methods[0];
  }

  const [highlightedMethod, setHighlightedMethod] = useState(initialHighlightedMethod);

  /**
   * Sets the current highlighted method as the selected method, which causes the steps to re-render
   * and proceed to the "next" screen.
   */
  function handleGoToNext() {
    onSelectMethod(highlightedMethod);
  }

  useEffect(() => {
    if (initialHighlightedMethod) {
      handleGoToNext();
    }
  }, []);

  /**
   * Handle clicking on a method
   */
  function handleClick(method) {
    setHighlightedMethod(method);
  }

  /**
   * Send the user back to the introduction screen
   */
  function handleBack() {
    if (onClickBack) {
      onClickBack();
    }
  }

  function renderActions() {
    return <ul className="mfa-action-list">
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
    </ul>;
  }

  // Render the component
  const classes = classnames('mfa-method-tile-group', {
    'mfa-method-tile-group--three-columns': methods.length % 3 === 0,
  });
  return <div>
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
  </div>;
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
