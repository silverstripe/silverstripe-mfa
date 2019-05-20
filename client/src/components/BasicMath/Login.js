/* global window */

import React, { Component } from 'react';
import LoadingIndicator from 'components/LoadingIndicator';

/**
 * This component provides the user interface for registering backup codes with a user. This process
 * only involves showing the user the backup codes. User input is not required to set up the codes.
 */
class Login extends Component {
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
    const { onCompleteVerification, moreOptionsControl, numbers } = this.props;
    const { ss: { i18n } } = window;

    if (!numbers) {
      return <div className="mfa-loader"><LoadingIndicator /></div>;
    }

    return (
      <div className="mfa-register-backup-codes__container">
        <label style={{ display: 'block' }} htmlFor="answer">{this.renderQuestion()}</label>
        <input id="answer" type="text" value={this.state.answer} onChange={this.handleChange} />
        <div>
          <button
            className="btn btn-primary"
            onClick={() => onCompleteVerification({ answer: this.state.answer })}
          >
            {i18n._t('BasicMathLogin.FINISH', 'Finish')}
          </button>
          { moreOptionsControl }
        </div>
      </div>
    );
  }
}

export default Login;
