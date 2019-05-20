import MFA_REGISTER from './actionTypes';

/**
 * Adjust state to show a specified screen. @see components/Register exports for screens
 *
 * @param {number} screen
 */
export const showScreen = screen => ({
  type: MFA_REGISTER.SET_SCREEN,
  payload: { screen },
});

/**
 * Adjust the current method being registered following @see types/availableMethod
 *
 * @param {Object} method
 */
export const chooseMethod = method => ({
  type: MFA_REGISTER.SET_METHOD,
  payload: { method },
});

/**
 * Adjust the methods that are available for registration with a Register app. Provided as an array
 * of objects matching @see types/availableMethod
 *
 * @param {Array} methods
 */
export const setAvailableMethods = methods => ({
  type: MFA_REGISTER.SET_AVAILABLE_METHODS,
  payload: { availableMethods: methods },
});

/**
 * Add a method (@see types/availableMethod) that is available for registration with a Register app
 *
 * @param {Object} method
 */
export const addAvailableMethod = method => ({
  type: MFA_REGISTER.ADD_AVAILABLE_METHOD,
  payload: { method },
});

/**
 * Remove a method (@see types/availableMethod) so it is no longer available for registration with a
 * Register app
 *
 * @param {Object} method
 */
export const removeAvailableMethod = method => ({
  type: MFA_REGISTER.REMOVE_AVAILABLE_METHOD,
  payload: { method },
});
