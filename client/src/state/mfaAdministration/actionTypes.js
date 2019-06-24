export default [
  'ADD_REGISTERED_METHOD',
  'REMOVE_REGISTERED_METHOD',
  'SET_DEFAULT_METHOD',
  'SET_REGISTERED_METHODS',
].reduce((obj, item) => Object.assign(obj, { [item]: `MFA_ADMINISTRATION.${item}` }), {});
