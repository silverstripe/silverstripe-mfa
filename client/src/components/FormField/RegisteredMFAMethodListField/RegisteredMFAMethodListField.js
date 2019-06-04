import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Button, Modal, ModalBody, ModalHeader } from 'reactstrap';
import { inject } from 'lib/Injector'; // eslint-disable-line
import { compose } from 'redux';
import { connect } from 'react-redux';
import classnames from 'classnames';
import methodShape from 'types/registeredMethod';
import AccountResetUI from './AccountResetUI';
import MethodListItem from './MethodListItem';
import {
  addAvailableMethod,
  chooseMethod,
  setAvailableMethods,
  showScreen,
} from 'state/mfaRegister/actions';
import {
  SCREEN_CHOOSE_METHOD,
  SCREEN_REGISTER_METHOD,
  SCREEN_INTRODUCTION
} from 'components/Register';
import Title from '../../Register/Title';
import Config from 'lib/Config'; // eslint-disable-line
import confirm from '@silverstripe/reactstrap-confirm';

const fallbacks = require('../../../../lang/src/en.json');

class RegisteredMFAMethodListField extends Component {
  constructor(props) {
    super(props);

    const { initialAvailableMethods, registeredMethods } = props;

    // Move registered methods into state as we might remove and add them during the lifetime of
    // this component.
    this.state = {
      modalOpen: false,
      registeredMethods,
    };

    props.onUpdateAvailableMethods(initialAvailableMethods);

    this.handleToggleModal = this.handleToggleModal.bind(this);
    this.handleClickRemove = this.handleClickRemove.bind(this);
    this.handleAddMethod = this.handleAddMethod.bind(this);
    this.handleRemoveBackupMethod = this.handleRemoveBackupMethod.bind(this);
    this.handleRemoveMethod = this.handleRemoveMethod.bind(this);
    this.handleResetMethod = this.handleResetMethod.bind(this);
  }

  componentDidUpdate() {
    const { registrationScreen } = this.props;
    const { modalOpen } = this.state;

    // Close the modal if the user returns to the introduction screen (ie. they click "back").
    if (registrationScreen === SCREEN_INTRODUCTION && modalOpen) {
      this.handleToggleModal();
    }
  }

  /**
   * The backup and default methods are rendered separately
   * @returns {Array<object>}
   */
  getBaseMethods() {
    const { backupMethod, defaultMethod } = this.props;
    let { registeredMethods: methods } = this.state;

    if (backupMethod) {
      methods = methods.filter(method => method.urlSegment !== backupMethod.urlSegment);
    }

    if (defaultMethod) {
      methods = methods.filter(method => method.urlSegment !== defaultMethod.urlSegment);
    }

    return methods;
  }

  /**
   * Handle a request to toggle the modal. This method will also reset the modal provided it's being
   * opened, unless `true` is provided as the parameter
   *
   * @param {boolean} skipReset
   */
  handleToggleModal(skipReset = false) {
    const { modalOpen } = this.state;

    this.setState({
      modalOpen: !modalOpen,
    });

    if (!modalOpen && skipReset !== true) {
      // Dispatch a redux action to reset the state of the Register app
      this.props.onResetRegister();
    }
  }

  /**
   * Handle a request to add the given method as a registered method
   *
   * @param {object} method
   */
  handleAddMethod(method) {
    this.setState(state => {
      const { registeredMethods } = state;

      // Look for the method if it's existing
      const existingMethodIndex = registeredMethods.findIndex(
        candidate => candidate.urlSegment === method.urlSegment
      );

      if (existingMethodIndex >= 0) {
        // Replace the existing method
        registeredMethods[existingMethodIndex] = method;
        return { registeredMethods };
      }

      // Otherwise append the new registered method
      return {
        registeredMethods: [
          ...registeredMethods,
          method,
        ]
      };
    });
  }

  /**
   * Handle a request to remove the given method as a registered method
   *
   * @param {object} method
   */
  handleRemoveMethod(method) {
    const { onAddAvailableMethod } = this.props;

    this.setState(state => ({
      registeredMethods: state.registeredMethods.filter(
        candidate => candidate.urlSegment !== method.urlSegment
      ),
    }));
    onAddAvailableMethod(method);
  }

  /**
   * Handle a request to remove the backup method as a registered method
   */
  handleRemoveBackupMethod() {
    const { backupMethod } = this.props;

    // It's possible that a backup method is not configured
    if (!backupMethod) {
      return;
    }

    // Search for the backup method amongst the currently registered methods
    const { registeredMethods } = this.state;
    const backupMethodIndex = registeredMethods.findIndex(
      candidate => candidate.urlSegment === backupMethod.urlSegment
    );

    // The backup method isn't even registered
    if (backupMethodIndex < 0) {
      return;
    }

    // Remove the method and set it back into state
    registeredMethods.splice(backupMethodIndex, 1);
    this.setState({
      registeredMethods
    });
  }

  /**
   * Handle a click event to remove the given method. This will prompt the user and call the
   * relevant endpoint to remove the method
   *
   * @param {object} method
   */
  async handleClickRemove(method) {
    const { endpoints: { remove } } = this.props;
    const { ss: { i18n } } = window;

    // Can't remove if there's no endpoint. UI should prevent this from happening
    if (!remove) {
      throw Error('Cannot remove method as no remove endpoint is provided');
    }

    // Confirm with the user
    const confirmMessage = i18n._t(
      'MultiFactorAuthentication.DELETE_CONFIRMATION',
      fallbacks['MultiFactorAuthentication.DELETE_CONFIRMATION']
    );
    const confirmTitle = i18n._t(
      'MultiFactorAuthentication.CONFIRMATION_TITLE',
      fallbacks['MultiFactorAuthentication.CONFIRMATION_TITLE']
    );
    const buttonLabel = i18n._t(
      'MultiFactorAuthentication.DELETE_CONFIRMATION_BUTTON',
      fallbacks['MultiFactorAuthentication.DELETE_CONFIRMATION_BUTTON']
    );

    if (!await confirm(confirmMessage, { title: confirmTitle, confirmLabel: buttonLabel })) {
      return;
    }

    const token = Config.get('SecurityID');
    const endpoint = `${remove.replace('{urlSegment}', method.urlSegment)}?SecurityID=${token}`;

    fetch(endpoint).then(response => response.json().then(json => {
      if (response.status === 200) {
        this.handleRemoveMethod(json.availableMethod);

        if (!json.hasBackupMethod) {
          this.handleRemoveBackupMethod();
        }

        return;
      }

      const message = (json.errors && ` Errors: \n - ${json.errors.join('\n -')}`) || '';
      throw Error(`Could not delete method. Error code ${response.status}.${message}`);
    }));
  }

  /**
   * Handle a request to reset the given method. This will just begin the registration process for
   * the given method, even if it's not an "availableMethod". The backend endpoint should allow
   * overwriting the existing registration with a new one.
   *
   * @param {object} method
   */
  handleResetMethod(method) {
    const registerableMethod = this.props.allAvailableMethods.find(
      candidate => candidate.urlSegment === method.urlSegment
    );

    if (!registerableMethod) {
      throw Error(`Cannot register the method given: ${method.name} (${method.urlSegment}).`);
    }

    this.props.onResetMethod(registerableMethod);
    this.handleToggleModal(true);
  }

  /**
   * Render a message that should appear when no methods are registered.
   *
   * @return {HTMLElement|null}
   */
  renderNoMethodsMessage() {
    if (this.getBaseMethods().length) {
      return null;
    }

    const { readOnly } = this.props;
    const { ss: { i18n } } = window;
    const messageKey = readOnly
      ? 'MultiFactorAuthentication.NO_METHODS_REGISTERED_READONLY'
      : 'MultiFactorAuthentication.NO_METHODS_REGISTERED';

    return (
      <div className="registered-mfa-method-list-field__no-methods">
        {i18n._t(messageKey, fallbacks[messageKey])}
      </div>
    );
  }

  /**
   * Render a MethodListItem for the registered backup method
   *
   * @return {MethodListItem}
   */
  renderBackupMethod() {
    const { backupMethod, backupCreatedDate } = this.props;
    const { registeredMethods } = this.state;
    const { ss: { i18n } } = window;

    if (!backupMethod || !registeredMethods.find(
      candidate => candidate.urlSegment === backupMethod.urlSegment
    )) {
      return '';
    }

    // Overload onReset to confirm with user for backups only
    const confirmMessage = i18n._t(
      'MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION',
      fallbacks['MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION']
    );
    const confirmTitle = i18n._t(
      'MultiFactorAuthentication.CONFIRMATION_TITLE',
      fallbacks['MultiFactorAuthentication.CONFIRMATION_TITLE']
    );
    const buttonLabel = i18n._t(
      'MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION_BUTTON',
      fallbacks['MultiFactorAuthentication.RESET_BACKUP_CONFIRMATION_BUTTON']
    );

    const handleReset = async method => {
      if (!await confirm(confirmMessage, { title: confirmTitle, confirmLabel: buttonLabel })) {
        return;
      }
      this.handleResetMethod(method);
    };

    return (
      <MethodListItem
        method={backupMethod}
        onResetMethod={handleReset}
        createdDate={backupCreatedDate}
        isReadOnly={false}
        isBackupMethod
        tag="div"
        className="registered-method-list-item--backup"
      />
    );
  }

  /**
   * Return a list of renderable MethodListItems for the list of registered methods
   *
   * @return {Array<MethodListItem>}
   */
  renderBaseMethods() {
    const baseMethods = this.getBaseMethods();

    if (!baseMethods.length) {
      return '';
    }

    const { defaultMethod, endpoints } = this.props;

    return baseMethods
      .map(method => {
        const props = {
          method,
          key: method.name,
          isDefaultMethod: defaultMethod && method.urlSegment === defaultMethod.urlSegment,
          isReadOnly: false,
          onRemoveMethod: endpoints && endpoints.remove && this.handleClickRemove,
          onResetMethod: this.handleResetMethod,
        };

        return <MethodListItem {...props} />;
      });
  }

  /**
   * Render a Reactstrap modal that contains the Register component used to (re-)register MFA
   * methods
   *
   * @return {Modal}
   */
  renderModal() {
    const {
      backupMethod,
      endpoints,
      resources,
      registrationScreen,
      RegisterComponent,
    } = this.props;

    const { registeredMethods } = this.state;
    const { ss: { i18n } } = window;

    const completeMessage = i18n._t(
      'MultiFactorAuthentication.ADMIN_SETUP_COMPLETE_CONTINUE',
      fallbacks['MultiFactorAuthentication.ADMIN_SETUP_COMPLETE_CONTINUE']
    );


    return (
      <Modal
        isOpen={this.state.modalOpen}
        toggle={this.handleToggleModal}
        className="registered-mfa-method-list-field-register-modal"
      >
        <ModalHeader toggle={this.handleToggleModal}><Title Tag={null} /></ModalHeader>
        <ModalBody className="registered-mfa-method-list-field-register-modal__content">
          {registrationScreen !== SCREEN_INTRODUCTION && (<RegisterComponent
            backupMethod={backupMethod}
            registeredMethods={registeredMethods}
            onCompleteRegistration={this.handleToggleModal}
            onRegister={this.handleAddMethod}
            resources={resources}
            endpoints={endpoints}
            showTitle={false}
            showSubTitle={false}
            completeMessage={completeMessage}
          />)}
        </ModalBody>
      </Modal>
    );
  }

  renderAddButton() {
    const { availableMethods, registeredMethods, readOnly } = this.props;

    if (readOnly || !availableMethods || availableMethods.length === 0) {
      return null;
    }

    const { ss: { i18n } } = window;
    const label = registeredMethods.length
      ? i18n._t(
        'MultiFactorAuthentication.ADD_ANOTHER_METHOD',
        fallbacks['MultiFactorAuthentication.ADD_ANOTHER_METHOD']
      )
      : i18n._t(
        'MultiFactorAuthentication.ADD_FIRST_METHOD',
        fallbacks['MultiFactorAuthentication.ADD_FIRST_METHOD']
      );

    return (
      <Button
        className="registered-mfa-method-list-field__button"
        outline
        onClick={this.handleToggleModal}
      >
        { label }
      </Button>
    );
  }

  render() {
    const { readOnly, resetEndpoint } = this.props;
    const classNames = classnames({
      'registered-mfa-method-list-field': true,
      'registered-mfa-method-list-field--read-only': readOnly,
    });

    return (
      <div className={classNames}>
        <ul className="method-list">
          { this.renderBaseMethods() }
        </ul>
        { this.renderNoMethodsMessage() }
        { this.renderAddButton() }
        { this.renderBackupMethod() }
        { readOnly && <hr /> }
        { readOnly && <AccountResetUI resetEndpoint={resetEndpoint} /> }
        { this.renderModal() }
      </div>
    );
  }
}

RegisteredMFAMethodListField.propTypes = {
  backupMethod: methodShape,
  defaultMethod: methodShape,
  readOnly: PropTypes.bool,
  initialAvailableMethods: PropTypes.arrayOf(methodShape),
  registeredMethods: PropTypes.arrayOf(methodShape).isRequired,
  resetEndpoint: PropTypes.string,

  endpoints: PropTypes.shape({
    register: PropTypes.string,
    remove: PropTypes.string,
  }),

  // Injected components
  RegisterComponent: PropTypes.oneOfType([PropTypes.element, PropTypes.func]),
};

RegisteredMFAMethodListField.defaultProps = {
  initialAvailableMethods: [],
};

const mapDispatchToProps = dispatch => ({
  onResetRegister: () => {
    dispatch(chooseMethod(null));
    dispatch(showScreen(SCREEN_CHOOSE_METHOD));
  },
  onUpdateAvailableMethods: methods => {
    dispatch(setAvailableMethods(methods));
  },
  onAddAvailableMethod: method => {
    dispatch(addAvailableMethod(method));
  },
  onResetMethod: method => {
    dispatch(chooseMethod(method));
    dispatch(showScreen(SCREEN_REGISTER_METHOD));
  },
});

const mapStateToProps = state => {
  const source = state.mfaRegister || state;

  return {
    availableMethods: source.availableMethods,
    registrationScreen: source.screen,
  };
};

export { RegisteredMFAMethodListField as Component };

export default compose(
  inject(
    ['MFARegister'],
    (RegisterComponent) => ({
      RegisterComponent,
    }),
    () => 'RegisteredMFAMethodListField'
  ),
  connect(mapStateToProps, mapDispatchToProps)
)(RegisteredMFAMethodListField);
