/* global window */

import React, { Component } from 'react';
import PropTypes from 'prop-types';
import Printd from 'printd';
import { CopyToClipboard } from 'react-copy-to-clipboard';
import { formatCode } from 'lib/formatCode';

/**
 * This component provides the user interface for registering backup codes with a user. This process
 * only involves showing the user the backup codes. User input is not required to set up the codes.
 */
class Register extends Component {
  constructor(props) {
    super(props);

    this.state = {
      recentlyCopied: false
    };

    // Prepare a ref to use for the DOM node that will be printed
    this.printRef = React.createRef();
    // Prepare a class member to store a timeout ref that provides feedback on copy to clipboard
    this.copyMessageTimeout = null;

    this.handlePrint = this.handlePrint.bind(this);
    this.handleCopy = this.handleCopy.bind(this);
  }

  /**
   * Get codes from component properties and format them with spaces every 3 (or 4) characters.
   * The number of characters in each group will never be less than 3 - the groups towards the end
   * will have four characters instead.
   *
   * @return {string[]}
   */
  getFormattedCodes() {
    const { codes } = this.props;

    return codes.map(code => formatCode(code));
  }

  /**
   * Handle an event triggered requesting the backup codes to be printed
   *
   * @param {Event} event
   */
  handlePrint(event) {
    event.preventDefault();

    (new Printd()).print(
      this.printRef.current,
      ['body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif }']
    );
  }

  /**
   * Handle an event triggered requesting the backup codes to be copied to clipboard
   *
   * @param {Event} event
   */
  handleCopy(event) {
    event.preventDefault();
    const { copyFeedbackDuration } = this.props;

    this.setState({
      recentlyCopied: true,
    });

    // Clear an existing timeout to reset the text on the copy link
    if (this.copyMessageTimeout) {
      clearTimeout(this.copyMessageTimeout);
    }

    // And set that timeout too
    this.copyMessageTimeout = setTimeout(() => {
      this.setState({
        recentlyCopied: false,
      });
    }, copyFeedbackDuration || 3000);
  }

  /**
   * Render a grid of formatted backup codes
   *
   * @return {HTMLElement}
   */
  renderCodes() {
    return (
      <div ref={this.printRef} className="mfa-register-backup-codes__code-grid">
        {this.getFormattedCodes().map(code => <div key={code}>{code}</div>)}
      </div>
    );
  }

  /**
   * Render the description for registering in with this method
   *
   * @return {HTMLElement}
   */
  renderDescription() {
    const { ss: { i18n } } = window;
    const { method } = this.props;

    return (
      <p>
        {i18n._t(
          'MFABackupCodesRegister.DESCRIPTION',
          'Recovery codes enable you to log into your account in the event your primary ' +
            'authentication is not available. Each code can only be used once. Store these codes ' +
            'somewhere safe, as they will not be viewable after this leaving this page.'
        )}
        &nbsp;
        <a
          href={method.supportLink}
          target="_blank"
          rel="noopener noreferrer"
        >
          {i18n._t('MFARegister.HELP', 'Find out more')}
        </a>
      </p>
    );
  }

  /**
   * Render the "print" action. A link allowing the user to trigger a print dialog for the codes
   *
   * @return {HTMLElement}
   */
  renderPrintAction() {
    const { ss: { i18n } } = window;

    return (
      <a href="#" onClick={this.handlePrint}>
        {i18n._t('MFABackupCodesRegister.PRINT', 'Print codes')}
      </a>
    );
  }

  /**
   * Render the "download" action. A link allowing the user to trigger a download of a text file
   * containing the codes
   *
   * @return {HTMLElement}
   */
  renderDownloadAction() {
    const { codes, method } = this.props;
    const { ss: { i18n } } = window;

    return (
      <a download={`${method.name}.txt`} href={`data:application/octet-stream,${codes.join('\n')}`}>
        {i18n._t('MFABackupCodesRegister.DOWNLOAD', 'Download')}
      </a>
    );
  }

  /**
   * Render the "copy" action. A link allowing the user to easily copy the backup codes to clipboard
   *
   * @return {CopyToClipboard}
   */
  renderCopyAction() {
    const { codes } = this.props;
    const { recentlyCopied } = this.state;
    const { ss: { i18n } } = window;

    const label = recentlyCopied
      ? i18n._t('MFABackupCodesRegister.COPY_RECENT', 'Copied!')
      : i18n._t('MFABackupCodesRegister.COPY', 'Copy codes');

    return (
      <CopyToClipboard text={codes.join('\n')}>
        <a
          href="#"
          className="mfa-register-backup-codes__copy-to-clipboard"
          onClick={this.handleCopy}
        >
          {label}
        </a>
      </CopyToClipboard>
    );
  }

  render() {
    const { onCompleteRegistration } = this.props;
    const { ss: { i18n } } = window;

    return (
      <div className="mfa-register-backup-codes__container">
        {this.renderDescription()}
        {this.renderCodes()}
        <div className="mfa-register-backup-codes__helper-links">
          {this.renderPrintAction()}
          {this.renderDownloadAction()}
          {this.renderCopyAction()}
        </div>
        <button className="btn btn-primary" onClick={() => onCompleteRegistration()}>
          {i18n._t('MFABackupCodesRegister.FINISH', 'Finish')}
        </button>
      </div>
    );
  }
}


Register.propTypes = {
  codes: PropTypes.arrayOf(PropTypes.string),
  copyFeedbackDuration: PropTypes.number,
};

export default Register;
