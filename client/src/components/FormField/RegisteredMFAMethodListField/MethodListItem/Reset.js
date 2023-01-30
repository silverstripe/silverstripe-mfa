import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import {
  SCREEN_CHOOSE_METHOD,
  SCREEN_INTRODUCTION,
  SCREEN_REGISTER_METHOD,
} from 'components/Register';
import { chooseMethod, showScreen } from 'state/mfaRegister/actions';
import registeredMethodShape from 'types/registeredMethod';
import availableMethodShape from 'types/availableMethod';
import RegisterModal from 'components/RegisterModal';

import fallbacks from '../../../../../lang/src/en.json';

class Reset extends Component {
  constructor(props) {
    super(props);

    this.state = {
      modalOpen: false,
    };

    this.handleReset = this.handleReset.bind(this);
    this.handleToggleModal = this.handleToggleModal.bind(this);
  }

  handleToggleModal() {
    this.setState(state => ({
      modalOpen: !state.modalOpen,
    }));
  }

  handleReset() {
    const { onResetMethod, method } = this.props;
    const { allAvailableMethods } = this.context;

    const availableMethodDetail = allAvailableMethods.find(
      candidate => candidate.urlSegment === method.urlSegment
    );

    if (!availableMethodDetail) {
      throw Error(`Cannot register the method given: ${method.name} (${method.urlSegment}).`);
    }

    onResetMethod(availableMethodDetail);
    this.handleToggleModal();
  }

  render() {
    const { onReset } = this.props;
    const { backupMethod, endpoints, resources } = this.context;

    const callback = onReset ? () => onReset(this.handleReset) : this.handleReset;

    return (
      <button
        className="registered-method-list-item__control"
        type="button"
        onClick={callback}
      >
        {window.ss.i18n._t(
          'MultiFactorAuthentication.RESET_METHOD',
          fallbacks['MultiFactorAuthentication.RESET_METHOD']
        )}
        <RegisterModal
          backupMethod={backupMethod}
          isOpen={this.state.modalOpen}
          toggle={this.handleToggleModal}
          resources={resources}
          endpoints={endpoints}
          disallowedScreens={[SCREEN_CHOOSE_METHOD, SCREEN_INTRODUCTION]}
        />
      </button>
    );
  }
}

Reset.propTypes = {
  method: registeredMethodShape.isRequired,
  onReset: PropTypes.func,
};

Reset.contextTypes = {
  allAvailableMethods: PropTypes.arrayOf(availableMethodShape),
  backupMethod: registeredMethodShape,
  endpoints: PropTypes.shape({
    register: PropTypes.string
  }),
  resources: PropTypes.object,
};

export default connect(null, dispatch => ({ onResetMethod: method => {
  dispatch(chooseMethod(method));
  dispatch(showScreen(SCREEN_REGISTER_METHOD));
} }))(Reset);
