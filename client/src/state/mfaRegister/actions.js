import MFA_REGISTER from './actionTypes';

export const showScreen = screen => ({
  type: MFA_REGISTER.SET_SCREEN,
  payload: { screen },
});

export const chooseMethod = method => ({
  type: MFA_REGISTER.SET_METHOD,
  payload: { method },
});
