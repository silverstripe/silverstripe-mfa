import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Button } from 'reactstrap';
import { connect } from 'react-redux';
import classnames from 'classnames';
import registeredMethodShape from 'types/registeredMethod';
import availableMethodShape from 'types/availableMethod';
import AccountResetUI from './AccountResetUI';
import MethodListItem from './MethodListItem';
import {
  chooseMethod,
  setAvailableMethods,
  showScreen,
} from 'state/mfaRegister/actions';
import { setRegisteredMethods } from 'state/mfaAdministration/actions';
import {
  SCREEN_CHOOSE_METHOD,
  SCREEN_INTRODUCTION
} from 'components/Register';
import RegisterModal from '../../RegisterModal';

const fallbacks = require('../../../../lang/src/en.json');

class RegisteredMFAMethodListField extends Component {
  constructor(props) {
    super(props);

    // Move registered methods into state as we might remove and add them during the lifetime of
    // this component.
    this.state = {
      modalOpen: false,
    };

    this.handleToggleModal = this.handleToggleModal.bind(this);
  }

  getChildContext() {
    const { allAvailableMethods, backupMethod, endpoints, resources } = this.props;

    return { allAvailableMethods, backupMethod, endpoints, resources };
  }

  componentDidMount() {
    const {
      onSetRegisteredMethods, initialRegisteredMethods,
      onUpdateAvailableMethods, initialAvailableMethods,
    } = this.props;

    onSetRegisteredMethods(initialRegisteredMethods);
    onUpdateAvailableMethods(initialAvailableMethods);
  }

  componentDidUpdate(prevProps, prevState) {
    const { registrationScreen } = this.props;
    const { modalOpen } = this.state;

    // Toggle the modal if the modal is open and the register screen has changed back to "intro"
    if (
      prevProps.registrationScreen !== registrationScreen
      && registrationScreen === SCREEN_INTRODUCTION
      && prevState.modalOpen
      && modalOpen
    ) {
      this.handleToggleModal();
    }
  }

  /**
   * The backup and default methods are rendered separately
   * @returns {Array<object>}
   */
  getBaseMethods() {
    const { backupMethod, defaultMethod } = this.props;
    let { registeredMethods: methods } = this.props;

    if (!methods) {
      return [];
    }

    if (backupMethod) {
      methods = methods.filter(method => method.urlSegment !== backupMethod.urlSegment);
    }

    if (defaultMethod) {
      methods = methods.filter(method => method.urlSegment !== defaultMethod.urlSegment);
    }

    return methods;
  }

  /**
   * Handle a request to toggle the modal
   */
  handleToggleModal() {
    this.setState(state => ({
      modalOpen: !state.modalOpen,
    }));
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
   * @return {MethodListItem|null}
   */
  renderBackupMethod() {
    const { backupMethod, backupCreatedDate, registeredMethods, readOnly } = this.props;

    if (!backupMethod) {
      return null;
    }

    const registeredBackupMethod = registeredMethods.find(
      candidate => candidate.urlSegment === backupMethod.urlSegment
    );

    // Assert there is a backup method and it's registered
    if (!registeredBackupMethod) {
      return null;
    }

    return (
      <MethodListItem
        method={registeredBackupMethod}
        createdDate={backupCreatedDate}
        canReset={!readOnly}
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
      return [];
    }

    const { defaultMethod, readOnly } = this.props;

    return baseMethods
      .map(method => {
        const props = {
          method,
          key: method.urlSegment,
          isDefaultMethod: defaultMethod && method.urlSegment === defaultMethod,
          canRemove: !readOnly,
          canReset: !readOnly,
        };

        return <MethodListItem {...props} />;
      });
  }

  /**
   * Render a Reactstrap modal that contains the Register component used to (re-)register MFA
   * methods
   *
   * @return {RegisterModal}
   */
  renderModal() {
    const {
      backupMethod,
      endpoints,
      resources,
    } = this.props;

    return (
      <RegisterModal
        backupMethod={backupMethod}
        isOpen={this.state.modalOpen}
        toggle={this.handleToggleModal}
        resources={resources}
        endpoints={endpoints}
      />
    );
  }

  /**
   * Render a button that will trigger the RegisterModal and allow adding new MFA methods
   *
   * @return {Button|null}
   */
  renderAddButton() {
    const { availableMethods, registeredMethods, readOnly, onResetRegister } = this.props;

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
        type="button"
        onClick={() => {
          this.handleToggleModal();
          onResetRegister();
        }}
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
  backupMethod: registeredMethodShape,
  defaultMethod: registeredMethodShape,
  readOnly: PropTypes.bool,
  initialRegisteredMethods: PropTypes.arrayOf(registeredMethodShape),
  initialAvailableMethods: PropTypes.arrayOf(availableMethodShape),
  allAvailableMethods: PropTypes.arrayOf(availableMethodShape),
  resetEndpoint: PropTypes.string,
  endpoints: PropTypes.shape({
    register: PropTypes.string,
    remove: PropTypes.string,
  }),
  resources: PropTypes.object,

  // Redux:
  availableMethods: PropTypes.arrayOf(availableMethodShape),
  registeredMethods: PropTypes.arrayOf(registeredMethodShape),
  registrationScreen: PropTypes.number,
};

RegisteredMFAMethodListField.defaultProps = {
  initialAvailableMethods: [],
};

RegisteredMFAMethodListField.childContextTypes = {
  allAvailableMethods: PropTypes.arrayOf(availableMethodShape),
  backupMethod: registeredMethodShape,
  endpoints: PropTypes.shape({
    register: PropTypes.string,
    remove: PropTypes.string,
  }),
  resources: PropTypes.object,
};

const mapDispatchToProps = dispatch => ({
  onResetRegister: () => {
    dispatch(chooseMethod(null));
    dispatch(showScreen(SCREEN_CHOOSE_METHOD));
  },
  onUpdateAvailableMethods: methods => {
    dispatch(setAvailableMethods(methods));
  },
  onSetRegisteredMethods: methods => {
    dispatch(setRegisteredMethods(methods));
  },
});

const mapStateToProps = state => {
  const { availableMethods, screen } = state.mfaRegister;
  const { registeredMethods } = state.mfaAdministration;

  return {
    availableMethods,
    registeredMethods: registeredMethods || [],
    registrationScreen: screen,
  };
};

export { RegisteredMFAMethodListField as Component };

export default connect(mapStateToProps, mapDispatchToProps)(RegisteredMFAMethodListField);
