import MFA_ADMINISTRATION from './actionTypes';

const initialState = {
  registeredMethods: [],
};

export default function mfaAdministationReducer(state = initialState, { type, payload } = {}) {
  const getMatcher = method => candidate => candidate.urlSegment === method.urlSegment;
  const { registeredMethods } = state;

  switch (type) {
    case MFA_ADMINISTRATION.ADD_REGISTERED_METHOD: {
      const { method } = payload;

      if (!Array.isArray(registeredMethods)) {
        return {
          ...state,
          registeredMethods: [method],
        };
      }

      if (registeredMethods.find(getMatcher(method))) {
        return state;
      }

      registeredMethods.push(method);

      return {
        ...state,
        registeredMethods
      };
    }

    case MFA_ADMINISTRATION.REMOVE_REGISTERED_METHOD: {
      const { method } = payload;
      const index = registeredMethods.findIndex(getMatcher(method));

      if (index < 0) {
        return state;
      }

      registeredMethods.splice(index, 1);

      return {
        ...state,
        // Ensure state is updated and not mutated
        registeredMethods: [...registeredMethods],
      };
    }

    case MFA_ADMINISTRATION.SET_REGISTERED_METHODS: {
      return {
        ...state,
        registeredMethods: payload.methods,
      };
    }

    default:
      return state;
  }
}
