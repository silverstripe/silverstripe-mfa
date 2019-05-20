import PropTypes from 'prop-types';
import React, { Component } from 'react';

import LoadingIndicator from '../../LoadingIndicator';

const fallbacks = require('../../../../lang/src/en.json');

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
    fetch(this.props.resetEndpoint, { method: 'POST' })
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
    const { complete, failure, submitting } = this.state;

    return (
      <div className="account-reset-ui">
        <h5>
          {
            i18n._t(
              'MultiFactorAuthentication.ACCOUNT_RESET_TITLE',
              fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_TITLE']
            )
          }
        </h5>

        <p>
          {
            i18n._t(
              'MultiFactorAuthentication.ACCOUNT_RESET_DESCRIPTION',
              fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_DESCRIPTION']
            )
          }
        </p>

        <p>
          {!submitting && !complete &&
            <button
              className="btn btn-outline-secondary"
              disabled={!this.props.resetEndpoint}
              onClick={this.onSendReset}
            >
              {
                i18n._t(
                  'MultiFactorAuthentication.ACCOUNT_RESET_ACTION',
                  fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_ACTION']
                )
              }
            </button>
          }

          { submitting &&
            <span style={{ display: 'flex', alignItems: 'center' }}>
              <LoadingIndicator size="3em" />
              &nbsp;
              {
                i18n._t(
                  'MultiFactorAuthentication.ACCOUNT_RESET_SENDING',
                  fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_SENDING']
                )
              }
            </span>
          }

          { complete && failure &&
            <span>
              {
                i18n._t(
                  'MultiFactorAuthentication.ACCOUNT_RESET_SENDING',
                  fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_SENDING_FAILURE']
                )
              }
            </span>
          }

          { complete && !failure &&
            <span>
              {
                i18n._t(
                  'MultiFactorAuthentication.ACCOUNT_RESET_SENDING',
                  fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_SENDING_SUCCESS']
                )
              }
            </span>
          }
        </p>
      </div>
    );
  }
}

AccountResetUI.propTypes = {
  resetEndpoint: PropTypes.string,
};

export default AccountResetUI;
