export default [
  'SET_ALL_METHODS',
].reduce((obj, item) => Object.assign(obj, { [item]: `MFA_VERIFY.${item}` }), {});
