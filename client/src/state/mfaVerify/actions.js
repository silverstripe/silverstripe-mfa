import MFA_VERIFY from './actionTypes';

/**
 * Set the list of all methods that are installed for use during the verification process.
 * This can be used for things like "withMethodAvailability" to determine which methods
 * are available from the browser's perspective.
 *
 * @param {array} methods
 */
export const setAllMethods = methods => ({
  type: MFA_VERIFY.SET_ALL_METHODS,
  payload: { allMethods: methods },
});
