import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Button, Modal, ModalHeader, ModalBody } from 'reactstrap';
import { inject } from 'lib/Injector';

import methodShape from '../../../types/registeredMethod';
import MethodListItem from './MethodListItem';

const fallbacks = require('../../../../lang/src/en.json');

class RegisteredMFAMethodListField extends Component {
  constructor(props) {
    super(props);

    this.state = {
      modalOpen: false,
    };

    this.handleToggleModal = this.handleToggleModal.bind(this);
  }

  handleToggleModal() {
    this.setState(state => ({
      modalOpen: !state.modalOpen,
    }));
  }

  /**
   * The backup and default methods are rendered separately
   * @returns {methodShape[]}
   */
  baseMethods() {
    const { backupMethod, defaultMethod } = this.props;
    let { registeredMethods: methods } = this.props;

    if (backupMethod) {
      methods = methods.filter(method => method.urlSegment !== backupMethod.urlSegment);
    }

    if (defaultMethod) {
      methods = methods.filter(method => method.urlSegment !== defaultMethod.urlSegment);
    }

    return methods;
  }

  renderBaseMethods() {
    return this.baseMethods()
      .map(method => (<MethodListItem method={method} key={method.name} />));
  }

  renderModal() {
    const {
      availableMethods,
      backupMethod,
      endpoints,
      registeredMethods,
      resources,
      RegisterComponent
    } = this.props;

    return (
      <Modal isOpen={this.state.modalOpen} toggle={this.handleToggleModal}>
        <ModalHeader toggle={this.handleToggleModal}>Test</ModalHeader>
        <ModalBody>
          <RegisterComponent
            availableMethods={availableMethods}
            backupMethod={backupMethod}
            registeredMethods={registeredMethods}
            onCompleteRegistration={this.handleToggleModal}
            resources={resources}
            endpoints={endpoints}
          />
        </ModalBody>
      </Modal>
    );
  }

  render() {
    const { ss: { i18n } } = window;
    const { defaultMethod } = this.props;

    const tEmpty = i18n._t(
      'MultiFactorAuthentication.NO_METHODS_REGISTERED',
      fallbacks['MultiFactorAuthentication.NO_METHODS_REGISTERED']
    );

    const tDefault = i18n._t(
      'MultiFactorAuthentication.DEFAULT',
      fallbacks['MultiFactorAuthentication.DEFAULT']
    );

    return (
      <div className="registered-mfa-method-list-field registered-mfa-method-list-field--read-only">
        <ul className="method-list">
          { !defaultMethod && this.baseMethods().length < 1 && (<li>{tEmpty}</li>) }
          { defaultMethod && (<MethodListItem method={defaultMethod} suffix={`(${tDefault})`} />) }
          { this.renderBaseMethods() }
        </ul>
        <Button outline onClick={this.handleToggleModal}>Add another MFA method</Button>
        { this.renderModal() }
      </div>
    );
  }
}

RegisteredMFAMethodListField.propTypes = {
  backupMethod: methodShape,
  defaultMethod: methodShape,
  // readOnly: PropTypes.bool,
  registeredMethods: PropTypes.arrayOf(methodShape).isRequired,
};


export default inject(
  ['MFARegister'],
  (RegisterComponent) => ({
    RegisterComponent,
  }),
  () => 'RegisteredMFAMethodListField'
)(RegisteredMFAMethodListField);
