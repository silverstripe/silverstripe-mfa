import {
  SCREEN_INTRODUCTION,
  SCREEN_CHOOSE_METHOD,
  SCREEN_REGISTER_METHOD
} from 'components/Register';
import MFA_REGISTER from './actionTypes';

const initialState = {
  screen: SCREEN_INTRODUCTION,
  method: null,
  availableMethods: [],
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

    case MFA_REGISTER.SET_AVAILABLE_METHODS: {
      return {
        ...state,
        availableMethods: payload.availableMethods,
      };
    }

    case MFA_REGISTER.ADD_AVAILABLE_METHOD: {
      return {
        ...state,
        availableMethods: [
          ...state.availableMethods,
          payload.method,
        ],
      };
    }

    case MFA_REGISTER.REMOVE_AVAILABLE_METHOD: {
      return {
        ...state,
        availableMethods: state.availableMethods.filter(
          method => method.urlSegment !== payload.method.urlSegment
        ),
      };
    }

    default:
      return state;
  }
}
