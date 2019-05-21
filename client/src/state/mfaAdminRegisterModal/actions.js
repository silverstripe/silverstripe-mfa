import MFA_ADMIN_REGISTER_MODAL from './actionTypes';

export const open = () => ({
  type: MFA_ADMIN_REGISTER_MODAL.OPEN,
});

export const close = () => ({
  type: MFA_ADMIN_REGISTER_MODAL.CLOSE,
});

export const toggle = () => ({
  type: MFA_ADMIN_REGISTER_MODAL.TOGGLE,
});
