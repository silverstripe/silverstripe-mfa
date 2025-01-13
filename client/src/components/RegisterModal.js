import React, { useEffect } from 'react';
import PropTypes from 'prop-types';
import { Modal, ModalBody, ModalHeader } from 'reactstrap';
import { inject } from 'lib/Injector'; // eslint-disable-line
import { compose } from 'redux';
import { connect } from 'react-redux';
import Title from 'components/Register/Title';
import { registerMethod, setDefaultMethod } from 'state/mfaAdministration/actions';
import registeredMethodShape from 'types/registeredMethod';
import { SCREEN_INTRODUCTION } from 'components/Register';

import fallbacks from '../../lang/src/en.json';

/**
 * Renders a modal that contains a register component. Given endpoints it will register MFA methods
 * and update redux state accordingly
 */
function RegisterModal(props) {
  const {
    backupMethod,
    disallowedScreens,
    endpoints,
    isOpen,
    onAddRegisteredMethod,
    RegisterComponent,
    registeredMethods,
    resources,
    onSetDefaultMethod,
    registrationScreen,
    toggle,
  } = props;

  useEffect(() => {
    if (!isOpen || !disallowedScreens.length) {
      return;
    }
    if (disallowedScreens.includes(registrationScreen)) {
      toggle();
    }
  }, [disallowedScreens, isOpen, registrationScreen, toggle]);

  function handleRegister(method) {
    if (!registeredMethods.length) {
      onSetDefaultMethod(method.urlSegment);
    }
    onAddRegisteredMethod(method);
  }

  // Render the component
  return <Modal
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
        onRegister={() => handleRegister()}
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
  </Modal>;
}

RegisterModal.propTypes = {
  // Boolean for if the modal is open
  isOpen: PropTypes.bool,
  // A function that is used to toggle the modal. This should affect the `isOpen` prop
  toggle: PropTypes.func,
  // Screens of the RegisterComponent that should not show. If they are shown while the modal is
  // open then the toggle function will be called
  disallowedScreens: PropTypes.arrayOf(PropTypes.number),

  // RegisterComponent props
  backupMethod: registeredMethodShape,
  resources: PropTypes.object,
  endpoints: PropTypes.shape({
    register: PropTypes.string,
  }),

  // Redux
  registrationScreen: PropTypes.number,
  registeredMethods: PropTypes.arrayOf(registeredMethodShape),
  onAddRegisteredMethod: PropTypes.func,
  onSetDefaultMethod: PropTypes.func,

  // Injector
  RegisterComponent: PropTypes.oneOfType([
    PropTypes.element,
    PropTypes.func,
    PropTypes.elementType,
  ]),
};

RegisterModal.defaultProps = {
  isOpen: false,
  disallowedScreens: [],
};

const mapStateToProps = state => ({
  registrationScreen: state.mfaRegister.screen,
  registeredMethods: state.mfaAdministration.registeredMethods,
});

const mapDispatchToProps = dispatch => ({
  onAddRegisteredMethod: method => { dispatch(registerMethod(method)); },
  onSetDefaultMethod: urlSegment => dispatch(setDefaultMethod(urlSegment)),
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
