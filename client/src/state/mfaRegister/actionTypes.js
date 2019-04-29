export default [
  'SET_SCREEN',
  'SET_METHOD',
].reduce((obj, item) => Object.assign(obj, { [item]: `MFA_REGISTER.${item}` }), {});
