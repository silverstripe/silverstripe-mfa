import PropTypes from 'prop-types';
import React, { useState, useEffect, useMemo } from 'react';
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
import { MFAMethodListContext } from './MFAMethodListContext';

import fallbacks from '../../../../lang/src/en.json';

const RegisteredMFAMethodListField = ({
  backupMethod,
  backupCreatedDate,
  defaultMethod,
  readOnly,
  isMFARequired,
  initialDefaultMethod,
  initialRegisteredMethods,
  initialAvailableMethods = [],
  allAvailableMethods,
  resetEndpoint,
  endpoints,
  resources,
  availableMethods,
  registeredMethods,
  MethodListItemComponent = MethodListItem,
  RegisterModalComponent = RegisterModal,
  onResetRegister,
  onUpdateAvailableMethods,
  onSetDefaultMethod,
  onSetRegisteredMethods,
}) => {
  const [modalOpen, setModalOpen] = useState(false);

  useEffect(() => {
    onSetRegisteredMethods(initialRegisteredMethods);
    onUpdateAvailableMethods(initialAvailableMethods);
    onSetDefaultMethod(initialDefaultMethod);
  }, []);

  /**
    * The backup method is rendered separately
    *
    * @returns {Array<object>}
    */
  const getBaseMethods = () => {
    let methods = registeredMethods;

    if (!methods) {
      return [];
    }

    if (backupMethod) {
      methods = methods.filter(method => method.urlSegment !== backupMethod.urlSegment);
    }

    return methods;
  };

  /**
    * Handle a request to toggle the modal
    */
  const handleToggleModal = () => {
    setModalOpen(state => !state);
  };

  /**
    * Render a message that should appear when no methods are registered.
    *
    * @return {HTMLElement|null}
    */
  const renderNoMethodsMessage = () => {
    if (getBaseMethods().length) {
      return null;
    }

    const { ss: { i18n } } = window;
    const messageKey = readOnly
      ? 'MultiFactorAuthentication.NO_METHODS_REGISTERED_READONLY'
      : 'MultiFactorAuthentication.NO_METHODS_REGISTERED';

    return (
      <div className="registered-mfa-method-list-field__no-methods">
        {i18n._t(messageKey, fallbacks[messageKey])}
      </div>
    );
  };

  /**
    * Render a MethodListItem for the registered backup method
    *
    * @return {MethodListItem|null}
    */
  const renderBackupMethod = () => {
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
  };

  /**
    * Return a list of renderable MethodListItems for the list of registered methods
    *
    * @return {Array<MethodListItem>}
    */
  const renderBaseMethods = () => {
    const baseMethods = getBaseMethods();

    if (!baseMethods.length) {
      return [];
    }

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
  };

  /**
    * Render a Reactstrap modal that contains the Register component used to (re-)register MFA
    * methods
    *
    * @return {RegisterModal}
    */
  const renderModal = () => (
    <RegisterModalComponent
      backupMethod={backupMethod}
      isOpen={modalOpen}
      toggle={handleToggleModal}
      resources={resources}
      endpoints={endpoints}
      disallowedScreens={[SCREEN_INTRODUCTION]}
    />
  );

  /**
    * Render a button that will trigger the RegisterModal and allow adding new MFA methods
    *
    * @return {Button|null}
    */
  const renderAddButton = () => {
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
          handleToggleModal();
          onResetRegister();
        }}
      >
        { label }
      </Button>
    );
  };

  const componentClassNames = classnames({
    'registered-mfa-method-list-field': true,
    'registered-mfa-method-list-field--read-only': readOnly,
  });

  // Wrapping in useMemo to avoid unnecessary re-renders of context consumers
  const contextValue = useMemo(() => ({
    allAvailableMethods,
    backupMethod,
    endpoints,
    resources,
  }), [allAvailableMethods, backupMethod, endpoints, resources]);

  return (
    <MFAMethodListContext.Provider value={contextValue}>
      <div className={componentClassNames}>
        <ul className="method-list">
          { renderBaseMethods() }
        </ul>
        { renderNoMethodsMessage() }
        { renderAddButton() }
        { renderBackupMethod() }
        { readOnly && <hr /> }
        { readOnly && <AccountResetUI resetEndpoint={resetEndpoint} /> }
        { renderModal() }
      </div>
    </MFAMethodListContext.Provider>
  );
};

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
