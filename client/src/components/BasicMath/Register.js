/* global window */

import React, { Component } from 'react';

/**
 * This component provides the user interface for registering backup codes with a user. This process
 * only involves showing the user the backup codes. User input is not required to set up the codes.
 */
class Register extends Component {
  constructor(props) {
    super(props);

    this.state = {
      secret: '',
    };

    this.handleChange = this.handleChange.bind(this);
  }

  handleChange(event) {
    this.setState({
      secret: event.target.value,
    });
  }

  render() {
    const { onCompleteRegistration } = this.props;
    const { ss: { i18n } } = window;

    return (
      <div className="mfa-register-backup-codes__container">
        <label htmlFor="secret">Enter a secret number:</label>
        <input id="secret" type="text" value={this.state.secret} onChange={this.handleChange} />
        <button
          className="btn btn-primary"
          onClick={() => onCompleteRegistration({ number: this.state.secret })}
        >
          {i18n._t('MFABackupCodesRegister.FINISH', 'Finish')}
        </button>
      </div>
    );
  }
}

export default Register;
