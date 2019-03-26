/* global window */

import React from 'react';
import PropTypes from 'prop-types';

const fallbacks = require('../../../lang/src/en.json');

function Introduction({ canSkip, onContinue, onSkip }) {
  const { ss: { i18n } } = window;

  return (
    <div>
      <h2 className="mfa-section-title">
        { i18n._t('MFAIntroduction.TITLE', fallbacks['MFAIntroduction.TITLE']) }
      </h2>

      <h4 className="mfa-feature-list-title">
        { i18n._t('MFAIntroduction.HOW_IT_WORKS', fallbacks['MFAIntroduction.HOW_IT_WORKS']) }
      </h4>

      <ul className="mfa-feature-list">
        <li className="mfa-feature-list-item">
          <img
            alt="Shields indicating additional protection"
            aria-hidden="true"
            className="mfa-feature-list-item__icon"
            src="/resources/vendor/silverstripe/mfa/client/dist/images/extra-protection.svg"
          />
          <div>
            <h5 className="mfa-block-heading mfa-feature-list-item__title">
              { i18n._t(
                'MFAIntroduction.EXTRA_LAYER_TITLE',
                fallbacks['MFAIntroduction.EXTRA_LAYER_TITLE']
              ) }
            </h5>

            <p className="mfa-feature-list-item__description">
              { i18n._t(
                'MFAIntroduction.EXTRA_LAYER_DESCRIPTION',
                fallbacks['MFAIntroduction.EXTRA_LAYER_DESCRIPTION']
              ) }
              &nbsp;
              <a href="#">{ i18n._t('MFA.FIND_OUT_MORE', fallbacks['MFA.FIND_OUT_MORE'])}</a>
            </p>
          </div>
        </li>

        <li className="mfa-feature-list-item">
          <img
            alt="Person with tick indicating uniqueness"
            aria-hidden="true"
            className="mfa-feature-list-item__icon"
            src="/resources/vendor/silverstripe/mfa/client/dist/images/unique.svg"
          />

          <div>
            <h5 className="mfa-block-heading mfa-feature-list-item__title">
              { i18n._t(
                'MFAIntroduction.UNIQUE_TITLE',
                fallbacks['MFAIntroduction.UNIQUE_TITLE']
              ) }
            </h5>

            <p className="mfa-feature-list-item__description">
              { i18n._t(
                'MFAIntroduction.UNIQUE_DESCRIPTION',
                fallbacks['MFAIntroduction.UNIQUE_DESCRIPTION']
              ) }
            </p>
          </div>
        </li>
      </ul>

      <ul className="mfa-action-list">
        <li className="mfa-action-list__item">
          <button className="mfa-action mfa-action--primary btn btn-primary" onClick={onContinue}>
            { i18n._t('MFAIntroduction.GET_STARTED', fallbacks['MFAIntroduction.GET_STARTED']) }
          </button>
        </li>

        {
          canSkip &&
          <li className="mfa-action-list__item">
            <button className="btn btn-secondary" onClick={onSkip}>
              { i18n._t('MFAIntroduction.SETUP_LATER', fallbacks['MFAIntroduction.SETUP_LATER']) }
            </button>
          </li>
        }
      </ul>
    </div>
  );
}

Introduction.propTypes = {
  canSkip: PropTypes.bool,
  onContinue: PropTypes.func.isRequired,
  onSkip: PropTypes.func,
};

export default Introduction;
