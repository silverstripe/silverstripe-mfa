import PropTypes from 'prop-types';
import React, { Component } from 'react';
import Config from 'lib/Config'; // eslint-disable-line
import api from 'lib/api';
import confirm from 'reactstrap-confirm';
import LoadingIndicator from '../../LoadingIndicator';
import CircleDash from '../../Icons/CircleDash';
import CircleTick from '../../Icons/CircleTick';

/**
 * regeneratorRuntime is not used explicitly in the code,
 * but this import is necessary for the async handleSendReset method to work correctly
 */
import regeneratorRuntime from 'regenerator-runtime'; // eslint-disable-line

import fallbacks from '../../../../lang/src/en.json';

/**
 * The AccountResetUI component is used to submit an Account Reset request.
 */
class AccountResetUI extends Component {
  constructor(props) {
    super(props);

    this.state = {
      complete: false,
      failed: false,
      submitting: false,
    };

    this.handleSendReset = this.handleSendReset.bind(this);
  }

  /**
   * Sends a reset request to the provided endpoint, and updates the component's state based on
   * the contents of the response.
   */
  async handleSendReset() {
    const { ss: { i18n } } = window;

    // Confirm with the user
    const confirmMessage = i18n._t(
      'MultiFactorAuthentication.ACCOUNT_RESET_CONFIRMATION',
      fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_CONFIRMATION']
    );
    const confirmTitle = i18n._t(
      'MultiFactorAuthentication.CONFIRMATION_TITLE',
      fallbacks['MultiFactorAuthentication.CONFIRMATION_TITLE']
    );
    const buttonLabel = i18n._t(
      'MultiFactorAuthentication.ACCOUNT_RESET_CONFIRMATION_BUTTON',
      fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_CONFIRMATION_BUTTON']
    );
    if (!await confirm({ title: confirmTitle, message: confirmMessage, confirmText: buttonLabel })) {
      return;
    }

    this.setState({ submitting: true });

    const body = JSON.stringify({ csrf_token: Config.get('SecurityID') });

    api(this.props.resetEndpoint, 'POST', body)
      .then(response => response.json())
      .then(output => {
        const failed = !!output.error;

        this.setState({ complete: true, failed, submitting: false });
      })
      .catch(() => {
        this.setState({ complete: true, failed: true, submitting: false });
      });
  }

  /**
   * Renders the reset request button if necessary, disabling it if an endpoint is not specified.
   *
   * @returns {null|HTMLElement}
   */
  renderAction() {
    const { ss: { i18n } } = window;
    const { resetEndpoint } = this.props;
    const { complete, submitting } = this.state;

    if (complete || submitting) {
      return null;
    }

    return (
      <p className="account-reset-action">
        <button
          className="btn btn-outline-secondary"
          disabled={!resetEndpoint}
          onClick={this.handleSendReset}
          type="button"
        >
          {
            i18n._t(
              'MultiFactorAuthentication.ACCOUNT_RESET_ACTION',
              fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_ACTION']
            )
          }
        </button>
      </p>
    );
  }

  /**
   * Renders the 'request in progress' status message.
   *
   * @returns {HTMLElement}
   */
  renderSending() {
    const { ss: { i18n } } = window;
    const { LoadingIndicatorComponent } = this.props;

    return (
      <p className="account-reset-action account-reset-action--sending">
        <span className="account-reset-action__icon">
          <LoadingIndicatorComponent size="32px" />
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
    );
  }

  /**
   * Renders the 'request failed' status message.
   *
   * @returns {HTMLElement}
   */
  renderFailure() {
    const { ss: { i18n } } = window;

    return (
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
    );
  }

  /**
   * Renders the 'request succeeded' status message.
   *
   * @returns {HTMLElement}
   */
  renderSuccess() {
    const { ss: { i18n } } = window;

    return (
      <p className="account-reset-action account-reset-action--success">
        <span className="account-reset-action__icon">
          <CircleTick size="32px" />
        </span>
        <span className="account-reset-action__message">
          {
            i18n._t(
              'MultiFactorAuthentication.ACCOUNT_RESET_SENDING_SUCCESS',
              fallbacks['MultiFactorAuthentication.ACCOUNT_RESET_SENDING_SUCCESS']
            )
          }
        </span>
      </p>
    );
  }

  /**
   * Checks whether a reset request has started / completed, and renders the current status if so.
   *
   * @returns {null|HTMLElement}
   */
  renderStatusMessage() {
    const { complete, failed, submitting } = this.state;

    if (submitting) {
      return this.renderSending();
    }

    if (!complete) {
      return null;
    }

    return (failed) ? this.renderFailure() : this.renderSuccess();
  }

  /**
   * Renders the full AccountResetUI component.
   *
   * @returns {HTMLElement}
   */
  render() {
    const { ss: { i18n } } = window;

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

        { this.renderAction() }

        { this.renderStatusMessage() }
      </div>
    );
  }
}

AccountResetUI.propTypes = {
  resetEndpoint: PropTypes.string,
  LoadingIndicatorComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
};

AccountResetUI.defaultProps = {
  LoadingIndicatorComponent: LoadingIndicator,
};

export default AccountResetUI;
