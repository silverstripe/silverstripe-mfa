/* global window */

import React, { Component } from 'react';
import LoadingIndicator from 'components/LoadingIndicator';

/**
 * This component provides the user interface for registering backup codes with a user. This process
 * only involves showing the user the backup codes. User input is not required to set up the codes.
 */
class Register extends Component {
  constructor(props) {
    super(props);

    this.state = {
      answer: '',
    };

    this.handleChange = this.handleChange.bind(this);
  }

  handleChange(event) {
    this.setState({
      answer: event.target.value,
    });
  }

  renderQuestion() {
    const { numbers } = this.props;

    return `What's the sum of ${numbers.join(', ')} and your secret number?`;
  }

  render() {
    const { onCompleteLogin, numbers } = this.props;
    const { ss: { i18n } } = window;

    if (!numbers) {
      return <LoadingIndicator />;
    }

    return (
      <div className="mfa-register-backup-codes__container">
        <label htmlFor="answer">{this.renderQuestion()}</label>
        <input id="answer" type="text" value={this.state.answer} onChange={this.handleChange} />
        <button
          className="btn btn-primary"
          onClick={() => onCompleteLogin({ answer: this.state.answer })}
        >
          {i18n._t('MFABackupCodesRegister.FINISH', 'Finish')}
        </button>
      </div>
    );
  }
}

export default Register;
