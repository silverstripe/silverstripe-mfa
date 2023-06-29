import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Button } from 'reactstrap';
import { connect } from 'react-redux';
import classnames from 'classnames';
import registeredMethodShape from 'types/registeredMethod';
import availableMethodShape from 'types/availableMethod';
import {
  chooseMethod,
  setAvailableMethods,
  showScreen,
} from 'state/mfaRegister/actions';
import { setDefaultMethod, setRegisteredMethods } from 'state/mfaAdministration/actions';
import {
  SCREEN_CHOOSE_METHOD,
  SCREEN_INTRODUCTION
} from 'components/Register';
import MethodListItem from './MethodListItem';
import AccountResetUI from './AccountResetUI';
import RegisterModal from '../../RegisterModal';

import fallbacks from '../../../../lang/src/en.json';

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
      onSetDefaultMethod, initialDefaultMethod,
      onSetRegisteredMethods, initialRegisteredMethods,
      onUpdateAvailableMethods, initialAvailableMethods,
    } = this.props;

    onSetRegisteredMethods(initialRegisteredMethods);
    onUpdateAvailableMethods(initialAvailableMethods);
    onSetDefaultMethod(initialDefaultMethod);
  }

  /**
   * The backup method is rendered separately
   *
   * @returns {Array<object>}
   */
  getBaseMethods() {
    const { backupMethod } = this.props;
    let { registeredMethods: methods } = this.props;

    if (!methods) {
      return [];
    }

    if (backupMethod) {
      methods = methods.filter(method => method.urlSegment !== backupMethod.urlSegment);
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
    const { backupMethod, backupCreatedDate, registeredMethods, readOnly, MethodListItemComponent } = this.props;

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
      <MethodListItemComponent
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
    const { isMFARequired } = this.props;
    const baseMethods = this.getBaseMethods();

    if (!baseMethods.length) {
      return [];
    }

    const { defaultMethod, readOnly, MethodListItemComponent } = this.props;

    return baseMethods
      .map(method => {
        const props = {
          method,
          key: method.urlSegment,
          isDefaultMethod: defaultMethod && method.urlSegment === defaultMethod,
          canRemove: !readOnly && !(isMFARequired && baseMethods.length === 1),
          canReset: !readOnly,
        };

        return <MethodListItemComponent {...props} />;
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
      RegisterModalComponent
    } = this.props;

    return (
      <RegisterModalComponent
        backupMethod={backupMethod}
        isOpen={this.state.modalOpen}
        toggle={this.handleToggleModal}
        resources={resources}
        endpoints={endpoints}
        disallowedScreens={[SCREEN_INTRODUCTION]}
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
  defaultMethod: PropTypes.string,
  readOnly: PropTypes.bool,
  isMFARequired: PropTypes.bool,
  initialDefaultMethod: PropTypes.string,
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
  MethodListItemComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
  RegisterModalComponent: PropTypes.oneOfType([PropTypes.object, PropTypes.func]),
};

RegisteredMFAMethodListField.defaultProps = {
  initialAvailableMethods: [],
  MethodListItemComponent: MethodListItem,
  RegisterModalComponent: RegisterModal
};

RegisteredMFAMethodListField.childContextTypes = {
  allAvailableMethods: PropTypes.arrayOf(availableMethodShape),
  backupMethod: registeredMethodShape,
  endpoints: PropTypes.shape({
    register: PropTypes.string,
    remove: PropTypes.string,
    setDefault: PropTypes.string,
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
  onSetDefaultMethod: urlSegment => {
    dispatch(setDefaultMethod(urlSegment));
  },
  onSetRegisteredMethods: methods => {
    dispatch(setRegisteredMethods(methods));
  },
});

const mapStateToProps = state => {
  const { availableMethods, screen } = state.mfaRegister;
  const { defaultMethod, registeredMethods } = state.mfaAdministration;

  return {
    availableMethods,
    defaultMethod,
    registeredMethods: registeredMethods || [],
    registrationScreen: screen,
  };
};

export { RegisteredMFAMethodListField as Component };

export default connect(mapStateToProps, mapDispatchToProps)(RegisteredMFAMethodListField);
