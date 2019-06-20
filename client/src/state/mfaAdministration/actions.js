import MFA_ADMINISTRATION from './actionTypes';

/**
 * Register a method to be included in the list of methods
 *
 * @param {Object} method
 */
export const registerMethod = method => ({
  type: MFA_ADMINISTRATION.ADD_REGISTERED_METHOD,
  payload: { method },
});

/**
 * Deregister a method and remove it from the list of registered methods
 *
 * @param {Object} method
 */
export const deregisterMethod = method => ({
  type: MFA_ADMINISTRATION.REMOVE_REGISTERED_METHOD,
  payload: { method },
});

/**
 * Set the default registered method
 *
 * @param {string} urlSegment
 */
export const setDefaultMethod = urlSegment => ({
  type: MFA_ADMINISTRATION.SET_DEFAULT_METHOD,
  payload: { defaultMethod: urlSegment },
});

/**
 * Set the list of registered methods
 *
 * @param {Array} methods
 */
export const setRegisteredMethods = methods => ({
  type: MFA_ADMINISTRATION.SET_REGISTERED_METHODS,
  payload: { methods },
});
