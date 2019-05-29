import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { Modal, ModalHeader, ModalBody, ModalFooter, Button } from 'reactstrap';

const fallbacks = require('../../lang/src/en.json');

class Confirmation extends Component {
  constructor(props) {
    super(props);
    this.state = {
      isOpen: true,
    };
  }

  render() {
    const { onConfirm, onCancel, title, body, confirmLabel } = this.props;
    const { isOpen } = this.state;

    const handleToggle = () => {
      if (typeof onCancel === 'function') {
        onCancel();
      }
      this.setState({
        isOpen: false,
      });
    };
    const handleConfirm = () => {
      onConfirm();
      this.setState({
        isOpen: false,
      });
    };

    const buttonLabel = confirmLabel || window.ss.i18n._t(
      'MultiFactorAuthentication.DEFAULT_CONFIRM_BUTTON',
      fallbacks['MultiFactorAuthentication.DEFAULT_CONFIRM_BUTTON']
    );

    return (
      <Modal isOpen={isOpen} toggle={handleToggle}>
        {title && <ModalHeader toggle={handleToggle}>{title}</ModalHeader>}
        <ModalBody>
          {body}
        </ModalBody>
        <ModalFooter>
          <Button color="primary" onClick={handleConfirm}>{buttonLabel}</Button>
        </ModalFooter>
      </Modal>
    );
  }
}

Confirmation.propTypes = {
  onConfirm: PropTypes.func.isRequired,
  body: PropTypes.string.isRequired,
  onCancel: PropTypes.func,
  title: PropTypes.string,
  confirmLabel: PropTypes.string,
};

export default Confirmation;
