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
   */
  renderMethods() {
    const { availableMethods } = this.props;

    if (!availableMethods) {
      return null;
    }

    return (
      <div>
        <h1>Register an authentication method</h1>
        <ul>
          {availableMethods.map(method => (
            <li key={method.urlSegment}>
              {method.description}
              <button onClick={this.getMethodRegistrationHandler(method)}>
                {method.name}
              </button>
              <a href={method.supportLink} target="_blank" rel="noopener noreferrer">Find out more</a>
            </li>
          ))}
        </ul>
      </div>
    );
  }

  render() {
    const { RegistrationComponent } = this.state;
    return (
      <div>
        <div className="mfa__log-out">
          <a href="Security/logout">Log out</a>
        </div>
        {RegistrationComponent || this.renderMethods()}
      </div>
    );
  }
}

Register.propTypes = {
  availableMethods: PropTypes.arrayOf(PropTypes.shape({
    urlSegment: PropTypes.string,
    name: PropTypes.string,
    description: PropTypes.string,
    supportLink: PropTypes.string,
  })),
};

export default Register;
