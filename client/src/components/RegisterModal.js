import React from 'react';
import PropTypes from 'prop-types';
import { Modal, ModalBody, ModalHeader } from 'reactstrap';
import { inject } from 'lib/Injector'; // eslint-disable-line
import { compose } from 'redux';
import { connect } from 'react-redux';
import Title from 'components/Register/Title';
import { registerMethod } from 'state/mfaAdministration/actions';
import registeredMethodShape from 'types/registeredMethod';
import { SCREEN_INTRODUCTION } from 'components/Register';

const fallbacks = require('../../lang/src/en.json');

/**
 * Renders a modal that contains a register component. Given endpoints it will register MFA methods
 * and update redux state accordingly
 */
const RegisterModal = ({
  backupMethod,
  endpoints,
  isOpen,
  toggle,
  onAddRegisteredMethod,
  registeredMethods,
  registrationScreen,
  resources,
  RegisterComponent
}) => (
  <Modal
    isOpen={isOpen}
    toggle={toggle}
    className="registered-mfa-method-list-field-register-modal"
  >
    <ModalHeader toggle={toggle}><Title Tag={null} /></ModalHeader>
    <ModalBody className="registered-mfa-method-list-field-register-modal__content">
      {registrationScreen !== SCREEN_INTRODUCTION && (<RegisterComponent
        backupMethod={backupMethod}
        registeredMethods={registeredMethods}
        onCompleteRegistration={toggle}
        onRegister={onAddRegisteredMethod}
        resources={resources}
        endpoints={endpoints}
        showTitle={false}
        showSubTitle={false}
        completeMessage={window.ss.i18n._t(
          'MultiFactorAuthentication.ADMIN_SETUP_COMPLETE_CONTINUE',
          fallbacks['MultiFactorAuthentication.ADMIN_SETUP_COMPLETE_CONTINUE']
        )}
      />)}
    </ModalBody>
  </Modal>
);


RegisterModal.propTypes = {
  backupMethod: registeredMethodShape,
  isOpen: PropTypes.bool,
  toggle: PropTypes.func,
  resources: PropTypes.object,
  endpoints: PropTypes.shape({
    register: PropTypes.string,
  }),

  // Redux
  registrationScreen: PropTypes.number,
  registeredMethods: PropTypes.arrayOf(registeredMethodShape),
  onAddRegisteredMethod: PropTypes.func,

  // Injector
  RegisterComponent: PropTypes.oneOfType([PropTypes.element, PropTypes.func]),
};

RegisterModal.defaultProps = {
  isOpen: false,
};

const mapStateToProps = state => ({
  registrationScreen: state.mfaRegister.screen,
  registeredMethods: state.mfaAdministration.registeredMethods,
});

const mapDispatchToProps = dispatch => ({
  onAddRegisteredMethod: method => { dispatch(registerMethod(method)); },
});

export { RegisterModal as Component };

export default compose(
  inject(
    ['MFARegister'],
    (RegisterComponent) => ({
      RegisterComponent,
    }),
    () => 'MFARegisterModal'
  ),
  connect(mapStateToProps, mapDispatchToProps)
)(RegisterModal);
