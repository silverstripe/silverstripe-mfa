import MFA_REGISTER from './actionTypes';
import {
  SCREEN_INTRODUCTION,
  SCREEN_CHOOSE_METHOD,
  SCREEN_REGISTER_METHOD,
} from 'components/Register';

const initialState = {
  screen: SCREEN_INTRODUCTION,
  method: null,
};

export default function mfaRegisterReducer(state = initialState, { type, payload } = {}) {
  switch (type) {
    case MFA_REGISTER.SET_SCREEN: {
      const { screen } = payload;

      // Coerce to "choose method" if trying to register a null method
      if (state.method === null && screen === SCREEN_REGISTER_METHOD) {
        return {
          ...state,
          screen: SCREEN_CHOOSE_METHOD
        };
      }
      return {
        ...state,
        screen,
      };
    }

    case MFA_REGISTER.SET_METHOD: {
      return {
        ...state,
        method: payload.method,
      };
    }

    default:
      return state;
  }
}
