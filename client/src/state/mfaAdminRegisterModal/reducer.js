import MFA_ADMIN_REGISTER_MODAL from './actionTypes';

const initialState = {
  open: false,
};

export default function mfaRegisterReducer(state = initialState, { type, payload } = {}) {
  switch (type) {
    case MFA_ADMIN_REGISTER_MODAL.OPEN: {
      return {
        ...state,
        open: true,
      };
    }

    case MFA_ADMIN_REGISTER_MODAL.CLOSE: {
      return {
        ...state,
        open: false,
      };
    }

    case MFA_ADMIN_REGISTER_MODAL.TOGGLE: {
      return {
        ...state,
        open: !state.open,
      };
    }

    default:
      return state;
  }
}
