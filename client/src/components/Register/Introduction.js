/* eslint-disable import/no-cycle */
/* global window */

import React from 'react';
import PropTypes from 'prop-types';
import { showScreen, chooseMethod } from 'state/mfaRegister/actions';
import { connect } from 'react-redux';
import { SCREEN_REGISTER_METHOD } from '../Register';
import Title from './Title';

import fallbacks from '../../../lang/src/en.json';

export const ActionList = ({ canSkip, onContinue, onSkip }) => {
  const { ss: { i18n } } = window;

  return (
    <ul className="mfa-action-list">
      <li className="mfa-action-list__item">
        <button className="btn btn-primary" onClick={onContinue}>
          { i18n._t('MultiFactorAuthentication.GET_STARTED', fallbacks['MultiFactorAuthentication.GET_STARTED']) }
        </button>
      </li>

      {
        canSkip &&
        <li className="mfa-action-list__item">
          <button className="btn btn-secondary" onClick={onSkip}>
            { i18n._t('MultiFactorAuthentication.SETUP_LATER', fallbacks['MultiFactorAuthentication.SETUP_LATER']) }
          </button>
        </li>
      }
    </ul>
  );
};

const Introduction = ({ canSkip, onContinue, onSkip, resources, showTitle, TitleComponent }) => {
  const { ss: { i18n } } = window;

  return (
    <div>
      { showTitle && <TitleComponent /> }

      <h4 className="mfa-feature-list-title">
        { i18n._t('MultiFactorAuthentication.HOW_IT_WORKS', fallbacks['MultiFactorAuthentication.HOW_IT_WORKS']) }
      </h4>

      <ul className="mfa-feature-list">
        <li className="mfa-feature-list-item">
          {
            resources.extra_factor_image_url &&
            <img
              alt={i18n._t('MultiFactorAuthentication.EXTRA_LAYER_IMAGE_ALT', fallbacks['MultiFactorAuthentication.EXTRA_LAYER_IMAGE_ALT'])}
              aria-hidden="true"
              className="mfa-feature-list-item__icon"
              src={resources.extra_factor_image_url}
            />
          }

          <div className="mfa-feature-list-item__content">
            <h5 className="mfa-block-heading mfa-feature-list-item__title">
              { i18n._t(
                'MultiFactorAuthentication.EXTRA_LAYER_TITLE',
                fallbacks['MultiFactorAuthentication.EXTRA_LAYER_TITLE']
              ) }
            </h5>

            <p className="mfa-feature-list-item__description">
              { i18n._t(
                'MultiFactorAuthentication.EXTRA_LAYER_DESCRIPTION',
                fallbacks['MultiFactorAuthentication.EXTRA_LAYER_DESCRIPTION']
              ) }
              &nbsp;
              {
                resources.user_help_link &&
                <a href={resources.user_help_link}>
                  { i18n._t('MultiFactorAuthentication.HOW_MFA_WORKS', fallbacks['MultiFactorAuthentication.HOW_MFA_WORKS']) }
                </a>
              }
            </p>
          </div>
        </li>

        <li className="mfa-feature-list-item">
          {
            resources.unique_image_url &&
            <img
              alt={i18n._t('MultiFactorAuthentication.UNIQUE_IMAGE_ALT', fallbacks['MultiFactorAuthentication.UNIQUE_IMAGE_ALT'])}
              aria-hidden="true"
              className="mfa-feature-list-item__icon"
              src={resources.unique_image_url}
            />
          }

          <div className="mfa-feature-list-item__content">
            <h5 className="mfa-block-heading mfa-feature-list-item__title">
              { i18n._t(
                'MultiFactorAuthentication.UNIQUE_TITLE',
                fallbacks['MultiFactorAuthentication.UNIQUE_TITLE']
              ) }
            </h5>

            <p className="mfa-feature-list-item__description">
              { i18n._t(
                'MultiFactorAuthentication.UNIQUE_DESCRIPTION',
                fallbacks['MultiFactorAuthentication.UNIQUE_DESCRIPTION']
              ) }
            </p>
          </div>
        </li>
      </ul>

      <ActionList
        canSkip={canSkip}
        onContinue={onContinue}
        onSkip={onSkip}
      />
    </div>
  );
};

Introduction.propTypes = {
  canSkip: PropTypes.bool,
  onContinue: PropTypes.func.isRequired,
  onSkip: PropTypes.func,
  resources: PropTypes.shape({
    user_help_link: PropTypes.string,
    extra_factor_image_url: PropTypes.string,
    unique_image_url: PropTypes.string,
  }).isRequired,
  showTitle: PropTypes.bool,
  TitleComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
};

Introduction.defaultProps = {
  showTitle: true,
  TitleComponent: Title
};

export { Introduction as Component };

const mapDispatchToProps = dispatch => ({
  onContinue: () => {
    // clear any existing methods from state
    dispatch(chooseMethod(null));
    dispatch(showScreen(SCREEN_REGISTER_METHOD));
  },
});

export default connect(null, mapDispatchToProps)(Introduction);
