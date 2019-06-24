import MFA_ADMINISTRATION from './actionTypes';

const initialState = {
  defaultMethod: null,
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

      // Update the default method in the store as well, if there is only
      // one method left after removing this one. There will also be a backup
      // code entry, and it will be last in the list.
      // We do not want to override existing state if there are more than 2
      // methods and the default method is already defined.
      const defaultMethodState = registeredMethods.length === 2 ? {
        defaultMethod: registeredMethods.find(() => true).urlSegment,
      } : {};

      return {
        ...state,
        ...defaultMethodState,
        // Ensure state is updated and not mutated
        registeredMethods: [...registeredMethods],
      };
    }

    case MFA_ADMINISTRATION.SET_DEFAULT_METHOD: {
      return {
        ...state,
        defaultMethod: payload.defaultMethod,
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
