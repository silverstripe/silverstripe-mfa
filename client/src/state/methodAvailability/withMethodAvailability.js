import React, { Component } from 'react';
import { connect } from 'react-redux';
import { compose } from 'redux';

const getDisplayName = (WrappedComponent) => WrappedComponent.displayName || WrappedComponent.name || 'Component';

/**
 * Takes an array of methods, and available method overrides from Redux, and provides
 * a getter for whether all methods are available for use.
 */
const withMethodAvailability = (WrappedComponent) => {
  const WithMethodAvailability = class extends Component {
    constructor(props) {
      super(props);

      this.getAvailabilityOverride = this.getAvailabilityOverride.bind(this);
      this.isAvailable = this.isAvailable.bind(this);
      this.getUnavailableMessage = this.getUnavailableMessage.bind(this);
    }

    /**
     * Checks "available method overrides", which can be provided via Redux state, and will
     * allow other components to specify client-side restrictions for various methods. For
     * example, WebAuthn is only available in certain browsers.
     *
     * @param {object|null} method If null, will use the method from props
     * @returns {object}
     */
    getAvailabilityOverride(method = null) {
      const { availableMethodOverrides } = this.props;
      const checkMethod = method || this.props.method;
      const { urlSegment } = checkMethod;

      if (typeof availableMethodOverrides[urlSegment] !== 'undefined') {
        return availableMethodOverrides[urlSegment];
      }
      return {};
    }

    /**
     * Returns a message to indicate why the method is unavailable. This comes either
     * from frontend initiated "availability overrides" (that come from Redux reductions)
     * or from the "method" prop's isAvailable key, in that order.
     *
     * @param {object|null} method If null, will use the method from props
     * @returns {string}
     */
    getUnavailableMessage(method = null) {
      const checkMethod = method || this.props.method;
      const availabilityOverride = this.getAvailabilityOverride(checkMethod);

      return availabilityOverride.unavailableMessage || checkMethod.unavailableMessage;
    }

    /**
     * Returns whether the current (or provided) method is available to be used. This
     * considers possible frontend overrides from Redux reductions first, then goes
     * to the method prop's data, which is server initiated.
     *
     * @param {object|null} method If null, will use the method from props
     * @returns {boolean}
     */
    isAvailable(method = null) {
      const checkMethod = method || this.props.method;
      const availabilityOverride = this.getAvailabilityOverride(checkMethod);

      // Default to backend
      let isAvailable = checkMethod.isAvailable;
      if (typeof availabilityOverride.isAvailable !== 'undefined') {
        // Prefer overridden "is available" value over that provided by the backend
        isAvailable = availabilityOverride.isAvailable;
      }

      return isAvailable;
    }

    render() {
      return (
        <WrappedComponent
          {...this.props}
          isAvailable={this.isAvailable}
          getUnavailableMessage={this.getUnavailableMessage}
        />
      );
    }
  };

  const displayName = getDisplayName(WrappedComponent);

  WithMethodAvailability.displayName = `WithMethodAvailability(${displayName})`;

  return WithMethodAvailability;
};

const mapStateToProps = state => {
  const methods = [...state.mfaRegister.availableMethods, ...state.mfaVerify.allMethods];

  const availableMethodOverrides = {};
  // Look for plugin MFA methods that have defined their own frontend availability
  // detection via redux reducers
  Object.values(methods).forEach(method => {
    const { urlSegment } = method;

    // Look for part of the state that matches this naming convention
    const stateKey = `${urlSegment}Availability`;
    if (typeof state[stateKey] !== 'undefined') {
      // Use the overrides over the backend schema data
      availableMethodOverrides[urlSegment] = state[stateKey];
    }
  });

  return {
    availableMethodOverrides,
  };
};

export { withMethodAvailability as hoc };

const composedWithMethodAvailability = compose(
  connect(mapStateToProps),
  withMethodAvailability
);

export default composedWithMethodAvailability;
