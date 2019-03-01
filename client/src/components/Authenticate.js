import React, { Component } from 'react';
import PropTypes from 'prop-types';

class Authenticate extends Component {
  constructor() {
    super();
    const { defaultMethod } = this.props;
    this.state = {
      ActiveMethod: null,
      otherMethods: [],
    };
    this.setActiveMethod.bind(this);
    this.setActiveMethod(defaultMethod);
  }

  /**
   * Set the current method the user will use to complete authentication
   * @param {Object} method
   */
  setActiveMethod(activeMethod) {
    const { registeredMethods } = this.props;
    this.setState({
      ActiveMethod: activeMethod.component, // inject me?
      otherMethods: registeredMethods.filter(
        // TODO: or suitably unique identifier other than 'name'
        registeredMethod => registeredMethod.name !== activeMethod.name
      )
    });
  }

  /**
   * If the half-logged in member has more than one authentication method set up, show a list of
   * others they have enabled that could also be used to complete authentication and log in.
   */
  renderOtherMethods() {
    const { otherMethods } = this.state;

    if (!otherMethods) {
      return null;
    }

    return (
      <div>
        <h2>Or choose another method</h2>
        <ul>
          {otherMethods.map(method => (
            <li>
              <button onClick={() => this.setActiveMethod(method)}>{method.name}</button>
            </li>
          ))}
        </ul>
      </div>
    );
  }

  render() {
    const { member } = this.props;
    const { ActiveMethod } = this.state;
    return (
      <div>
        <h1>Authenticate</h1>
        <ActiveMethod member={member} />
        {this.renderOtherMethods()}
      </div>
    );
  }
}

Authenticate.propTypes = {
  registeredMethods: PropTypes.arrayOf(PropTypes.object),
  defaultMethod: PropTypes.object,
};

export default Authenticate;
