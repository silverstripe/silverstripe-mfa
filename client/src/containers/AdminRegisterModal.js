import Register, { RegisterPropTypes } from 'components/Register';
import React from 'react';
import PropTypes from 'prop-types';
import { Modal, ModalHeader, ModalBody } from 'reactstrap';
import { toggle } from 'state/mfaAdminRegisterModal/actions';
import { connect } from 'react-redux';
import availableMethodType from 'types/availableMethod';

/**
 * Wraps a reactstrap Modal component the "Register" app for use in SilverStripe admin pages for
 * managing your own MFA settings while logged in
 */
const AdminRegisterModal = (props) => {
  const { open, onToggle, selectedMethod: { name }, schema } = props;

  return (
    <Modal isOpen={open} toggle={onToggle}>
      <ModalHeader toggle={onToggle}>{name}</ModalHeader>
      <ModalBody>
        <Register schema={schema} />
      </ModalBody>
    </Modal>
  );
};

AdminRegisterModal.propTypes = {
  schema: PropTypes.shape(RegisterPropTypes),

  // Redux props:
  open: PropTypes.bool,
  selectedMethod: availableMethodType,
  onToggle: PropTypes.func,
};

const mapStateToProps = state => ({
  selectedMethod: state.mfaRegister.method,
  open: state.mfaAdminRegisterModal.open,
});

const mapDispatchToProps = dispatch => ({
  onToggle: () => dispatch(toggle()),
});

export { AdminRegisterModal as Component };

export default connect(mapStateToProps, mapDispatchToProps)(AdminRegisterModal);
