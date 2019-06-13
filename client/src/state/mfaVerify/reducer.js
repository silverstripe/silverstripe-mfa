import MFA_VERIFY from './actionTypes';

const initialState = {
  allMethods: [],
};

export default function mfaRegisterReducer(state = initialState, { type, payload } = {}) {
  switch (type) {
    case MFA_VERIFY.SET_ALL_METHODS: {
      return {
        ...state,
        allMethods: payload.allMethods,
      };
    }

    default:
      return state;
  }
}
