import MFA_REGISTER from './actionTypes';

export const showScreen = screen => ({
  type: MFA_REGISTER.SET_SCREEN,
  payload: { screen },
});

export const chooseMethod = method => ({
  type: MFA_REGISTER.SET_METHOD,
  payload: { method },
});

export const setAvailableMethods = methods => ({
  type: MFA_REGISTER.SET_AVAILABLE_METHODS,
  payload: { availableMethods: methods },
});

export const addAvailableMethod = method => ({
  type: MFA_REGISTER.ADD_AVAILABLE_METHOD,
  payload: { method },
});

export const removeAvailableMethod = method => ({
  type: MFA_REGISTER.REMOVE_AVAILABLE_METHOD,
  payload: { method },
});
