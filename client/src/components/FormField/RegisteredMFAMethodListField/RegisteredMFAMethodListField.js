import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Button, Modal, ModalHeader, ModalBody } from 'reactstrap';
import { inject } from 'lib/Injector'; // eslint-disable-line
import { compose } from 'redux';
import { connect } from 'react-redux';
import classnames from 'classnames';
import methodShape from 'types/registeredMethod';

import AccountResetUI from './AccountResetUI';
import MethodListItem from './MethodListItem';
import { showScreen, chooseMethod, setAvailableMethods } from 'state/mfaRegister/actions';
import { SCREEN_CHOOSE_METHOD } from 'components/Register';
import Title from '../../Register/Title';

const fallbacks = require('../../../../lang/src/en.json');

class RegisteredMFAMethodListField extends Component {
  constructor(props) {
    super(props);

    this.state = {
      modalOpen: false,
    };

    props.onUpdateAvailableMethods(props.availableMethods);

    this.handleToggleModal = this.handleToggleModal.bind(this);
  }

  getAddMethodButtonLabel() {
    const { ss: { i18n } } = window;
    const { registeredMethods } = this.props;

    return registeredMethods.length
      ? i18n._t(
        'MultiFactorAuthentication.ADD_ANOTHER_METHOD',
        fallbacks['MultiFactorAuthentication.ADD_ANOTHER_METHOD']
      )
      : i18n._t(
        'MultiFactorAuthentication.ADD_FIRST_METHOD',
        fallbacks['MultiFactorAuthentication.ADD_FIRST_METHOD']
      );
  }

  /**
   * Handles an event/request to toggle the visibility of the register modal.
   */
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
      backupMethod,
      endpoints,
      registeredMethods,
      resources,
      RegisterComponent
    } = this.props;

    return (
      <Modal isOpen={this.state.modalOpen} toggle={this.handleToggleModal}>
        <ModalHeader toggle={this.handleToggleModal}><Title Tag={null} /></ModalHeader>
        <ModalBody>
          <RegisterComponent
            backupMethod={backupMethod}
            registeredMethods={registeredMethods}
            onCompleteRegistration={this.handleToggleModal}
            resources={resources}
            endpoints={endpoints}
            showTitle={false}
            showSubTitle={false}
          />
        </ModalBody>
      </Modal>
    );
  }

  render() {
    const { ss: { i18n } } = window;
    const { availableMethods, defaultMethod, readOnly, resetEndpoint } = this.props;

    const tEmpty = i18n._t(
      'MultiFactorAuthentication.NO_METHODS_REGISTERED',
      fallbacks['MultiFactorAuthentication.NO_METHODS_REGISTERED']
    );

    const tDefault = i18n._t(
      'MultiFactorAuthentication.DEFAULT',
      fallbacks['MultiFactorAuthentication.DEFAULT']
    );

    const classNames = classnames({
      'registered-mfa-method-list-field': true,
      'registered-mfa-method-list-field--read-only': readOnly,
    });

    return (
      <div className={classNames}>
        <ul className="method-list">
          { !defaultMethod && this.baseMethods().length < 1 && (<li>{tEmpty}</li>) }
          { defaultMethod && (<MethodListItem method={defaultMethod} suffix={`(${tDefault})`} />) }
          { this.renderBaseMethods() }

          <hr />

          { readOnly && <AccountResetUI resetEndpoint={resetEndpoint} /> }
        </ul>
        {
          availableMethods.length > 0 &&
          <Button
            className="registered-mfa-method-list-field__button"
            outline
            onClick={this.handleToggleModal}
          >
            { this.getAddMethodButtonLabel() }
          </Button>
        }
        { this.renderModal() }
      </div>
    );
  }
}

RegisteredMFAMethodListField.propTypes = {
  backupMethod: methodShape,
  defaultMethod: methodShape,
  readOnly: PropTypes.bool,
  availableMethods: PropTypes.arrayOf(methodShape),
  registeredMethods: PropTypes.arrayOf(methodShape).isRequired,
  resetEndpoint: PropTypes.string,

  // Injected components
  RegisterComponent: PropTypes.oneOfType([PropTypes.element, PropTypes.func]),
};

RegisteredMFAMethodListField.defaultProps = {
  availableMethods: [],
};

const mapDispatchToProps = dispatch => ({
  onResetRegister: () => {
    dispatch(chooseMethod(null));
    dispatch(showScreen(SCREEN_CHOOSE_METHOD));
  },
  onUpdateAvailableMethods: methods => {
    dispatch(setAvailableMethods(methods));
  },
});

export { RegisteredMFAMethodListField as Component };

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
