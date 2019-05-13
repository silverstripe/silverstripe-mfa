import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Button, Modal, ModalHeader, ModalBody } from 'reactstrap';
import { inject } from 'lib/Injector';
import { compose } from 'redux';
import { connect } from 'react-redux';
import methodShape from 'types/registeredMethod';
import MethodListItem from './MethodListItem';
import { showScreen, chooseMethod } from 'state/mfaRegister/actions';
import { SCREEN_CHOOSE_METHOD, SCREEN_REGISTER_METHOD } from 'components/Register';

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
    const { modalOpen } = this.state;

    this.setState({
      modalOpen: !modalOpen,
    });

    if (!modalOpen) {
      // Dispatch a redux action to reset the state of the Register app
      this.props.onResetRegister();
    }
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
    const { defaultMethod, availableMethods } = this.props;

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
        {
          availableMethods.length === 0 ||
          <Button outline onClick={this.handleToggleModal}>Add another MFA method</Button>
        }
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

const mapDispatchToProps = dispatch => ({
  onResetRegister: () => {
    dispatch(chooseMethod(null));
    dispatch(showScreen(SCREEN_CHOOSE_METHOD));
  }
});

export default compose(
  inject(
    ['MFARegister'],
    (RegisterComponent) => ({
      RegisterComponent,
    }),
    () => 'RegisteredMFAMethodListField'
  ),
  connect(null, mapDispatchToProps)
)(RegisteredMFAMethodListField);
