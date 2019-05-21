export default [
  'OPEN',
  'CLOSE',
  'TOGGLE',
].reduce((obj, item) => Object.assign(obj, { [item]: `MFA_ADMIN_REGISTER_MODAL.${item}` }), {});
