import PropTypes from 'prop-types';
import React, { Component } from 'react';
import Config from 'lib/Config';

import LoadingIndicator from '../../LoadingIndicator';
import CircleDash from '../../Icons/CircleDash';
import CircleTick from '../../Icons/CircleTick';

const fallbacks = require('../../../../lang/src/en.json');

/**
 * The AccountResetUI component is used to submit an Account Reset request.
 */
class AccountResetUI extends Component {
  constructor(props) {
    super(props);

    // Shift up into Redux if tidier
    this.state = {
      complete: false,
      failed: false,
      submitting: false,
    };

    this.onSendReset = this.onSendReset.bind(this);
  }

  onSendReset() {
    this.setState({ submitting: true });

    const body = JSON.stringify({ csrf_token: Config.get('SecurityID') });

    fetch(this.props.resetEndpoint, { method: 'POST', body })
      .then(response => response.json())
      .then(output => {
        const failure = !!output.error;

        this.setState({ complete: true, failure, submitting: false });
      })
      .catch(() => {
        this.setState({ complete: true, failure: true, submitting: false });
      });
  }

  render() {
    const { ss: { i18n } } = window;
    const { resetEndpoint } = this.props;
    const { complete, failure, submitting } = this.state;

    return (
      <div className="account-reset">
        <h5 className="account-reset__title">
          {
            i18n._t(
              'MultiFactorAuthentication.ACCOUNT_RESET_TITLE',
              fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_TITLE']
            )
          }
        </h5>

        <p className="account-reset__description">
          {
            i18n._t(
              'MultiFactorAuthentication.ACCOUNT_RESET_DESCRIPTION',
              fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_DESCRIPTION']
            )
          }
        </p>

        { !submitting && !complete &&
          <p className="account-reset-action">
            <button
              className="btn btn-outline-secondary"
              disabled={!resetEndpoint}
              onClick={this.onSendReset}
            >
              {
                i18n._t(
                  'MultiFactorAuthentication.ACCOUNT_RESET_ACTION',
                  fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_ACTION']
                )
              }
            </button>
          </p>
        }

        { submitting &&
          <p className="account-reset-action account-reset-action--sending">
            <span className="account-reset-action__icon">
              <LoadingIndicator size="32px" />
            </span>
            <span className="account-reset-action__message">
              {
                i18n._t(
                  'MultiFactorAuthentication.ACCOUNT_RESET_SENDING',
                  fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_SENDING']
                )
              }
            </span>
          </p>
        }

        { !submitting && complete && failure &&
          <p className="account-reset-action account-reset-action--failure">
            <span className="account-reset-action__icon">
              <CircleDash size="32px" />
            </span>
            <span className="account-reset-action__message">
              {
                i18n._t(
                  'MultiFactorAuthentication.ACCOUNT_RESET_SENDING',
                  fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_SENDING_FAILURE']
                )
              }
            </span>
          </p>
        }

        { !submitting && complete && !failure &&
          <p className="account-reset-action account-reset-action--success">
            <span className="account-reset-action__icon">
              <CircleTick size="32px" />
            </span>
            <span className="account-reset-action__message">
              {
                i18n._t(
                  'MultiFactorAuthentication.ACCOUNT_RESET_SENDING',
                  fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_SENDING_SUCCESS']
                )
              }
            </span>
          </p>
        }
      </div>
    );
  }
}

AccountResetUI.propTypes = {
  resetEndpoint: PropTypes.string,
};

export default AccountResetUI;
