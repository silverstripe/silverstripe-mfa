export default [
  'ADD_AVAILABLE_METHOD',
  'REMOVE_AVAILABLE_METHOD',
  'SET_AVAILABLE_METHODS',
  'SET_SCREEN',
  'SET_METHOD',
].reduce((obj, item) => Object.assign(obj, { [item]: `MFA_REGISTER.${item}` }), {});
