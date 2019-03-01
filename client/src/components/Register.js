import React, { Component } from 'react';
import PropTypes from 'prop-types';

class Register extends Component {
  constructor() {
    super();
    this.state = {
      RegistrationComponent: null
    };
  }

  /**
   * Set the MFA method the user is registering for
   * @param {Object} method
   */
  getMethodRegistrationHandler(method) {
    return () =>
      this.setState({
        RegistrationComponent: method.component // resolve with injector here?
      });
  }

  /**
   * If the site has more than one multi factor method enabled, show others a user can register
   * @param {Array} registerableMethods Available methods the user has not already set up
   */
  renderMethods(registerableMethods) {
    if (!registerableMethods) {
      return null;
    }

    return (
      <div>
        <h1>Register an authentication method</h1>
        <ul>
          {registerableMethods.map(method => (
            <li>
              <button onClick={this.getMethodRegistrationHandler(method)}>
                {method.name}
              </button>
            </li>
          ))}
        </ul>
      </div>
    );
  }

  render() {
    const {
      availableMethods,
    } = this.props;
    const { RegistrationComponent } = this.state;
    return (
      <div>
        <div className="mfa__log-out">
          <a href="Security/logout">Log out</a>
        </div>
        {RegistrationComponent || this.renderMethods(availableMethods)}
      </div>
    );
  }
}

Register.propTypes = {
  availableMethods: PropTypes.arrayOf(PropTypes.object),
};

export default Register;
