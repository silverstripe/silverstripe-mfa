/* global window */

import React, { Component } from 'react';
import classnames from 'classnames';

class Verify extends Component {
  constructor(props) {
    super(props);

    this.state = {
      value: '',
    };

    this.codeInput = React.createRef();

    this.handleChange = this.handleChange.bind(this);
    this.handleCompleteVerification = this.handleCompleteVerification.bind(this);
  }

  /**
   * Automatically set the focus to the code input field when the component is rendered
   */
  componentDidMount() {
    if (this.codeInput.current) {
      this.codeInput.current.focus();
    }
  }

  /**
   * Handle a change to the backup code input
   *
   * @param {Event} event
   */
  handleChange(event) {
    this.setState({
      value: event.target.value,
    });
  }

  /**
   * Handle pressing the "next" button after entering a backup code
   *
   * @param {Event} event
   */
  handleCompleteVerification(event) {
    event.preventDefault();

    const { onCompleteVerification } = this.props;

    onCompleteVerification({ code: this.state.value });
  }

  /**
   * Render the next button and any controls that are passed down from the parent
   *
   * @return {HTMLElement}
   */
  renderControls() {
    const { moreOptionsControl } = this.props;
    const { ss: { i18n } } = window;

    return (
      <ul className="mfa-action-list mfa-action-list--backup-codes">
        <li className="mfa-action-list__item">
          <button
            className="btn btn-primary"
            disabled={this.state.value.length === 0}
            onClick={this.handleCompleteVerification}
          >
            {i18n._t('MFABackupCodesVerify.NEXT', 'Next')}
          </button>
        </li>
        {moreOptionsControl && (
          <li className="mfa-action-list__item">
            {moreOptionsControl}
          </li>
        )}
      </ul>
    );
  }

  /**
   * Render a description for this input
   *
   * @return {HTMLElement}
   */
  renderDescription() {
    const { ss: { i18n } } = window;
    const { method } = this.props;

    return (
      <p>
        {i18n._t(
          'MFABackupCodesVerify.DESCRIPTION',
          'Use one of the recovery codes you received'
        )}
        &nbsp;
        {method && method.supportLink &&
          <a
            href={method.supportLink}
            target="_blank"
            rel="noopener noreferrer"
          >
            {i18n._t('MFARegister.RECOVERY_HELP', 'How to use recovery codes.')}
          </a>
        }
      </p>
    );
  }

  /**
   * Render the input for capturing the users backup code
   *
   * @return {HTMLElement}
   */
  renderInput() {
    const { error } = this.props;
    const { ss: { i18n } } = window;
    const label = i18n._t('MFABackupCodesVerify.LABEL', 'Enter recovery code');
    const formGroupClasses = classnames('mfa-verify-backup-codes__input-container', 'form-group', {
      'has-error': !!error,
    });

    return (
      <div className={formGroupClasses}>
        <label htmlFor="backup-code" className="form-label control-label">
          { label }
        </label>
        <input
          className="mfa-verify-backup-codes__input text form-control"
          type="text"
          placeholder={label}
          id="backup-code"
          ref={this.codeInput}
          onChange={this.handleChange}
        />
        {error && <div className="help-block">{error}</div>}
      </div>
    );
  }

  render() {
    const { graphic, name } = this.props;

    return (
      <form className="mfa-verify-backup-codes__container">
        <div className="mfa-verify-backup-codes__content">
          {this.renderDescription()}
          {this.renderInput()}
        </div>
        <div className="mfa-verify-backup-codes__image-holder">
          <img className="mfa-verify-backup-codes__image" src={graphic} alt={name} />
        </div>
        {this.renderControls()}
      </form>
    );
  }
}

export default Verify;
