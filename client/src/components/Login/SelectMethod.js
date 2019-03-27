import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import registeredMethodType from 'types/registeredMethod';

class SelectMethod extends PureComponent {
  /**
   * Returns controls that are rendered at the bottom of the panel (eg. the back button)
   *
   * @return {HTMLElement}
   */
  renderControls() {
    const { ss: { i18n } } = window;
    const { onClickBack } = this.props;

    return (
      <button className="mfa-login-select-method__back" onClick={onClickBack}>
        {i18n._t('MFALogin.BACK', 'Back')}
      </button>
    );
  }

  /**
   * Render the "last resort" message informing users what to do if they cannot use any given option
   *
   * @return {HTMLElement}
   */
  renderLastResortMessage() {
    const { ss: { i18n } } = window;

    return (
      <p>
        {i18n._t(
          'MFALogin.LAST_RESORT_MESSAGE',
          'Contact your site administrator if you require your multi-factor authentication to ' +
          'be reset'
        )}
      </p>
    );
  }

  /**
   * Render a list item for the given method
   *
   * @param {Object} method
   * @return {HTMLElement}
   */
  renderMethod(method) {
    const { onSelectMethod } = this.props;

    return (
      <li key={method.urlSegment}>
        <a href="#" onClick={onSelectMethod(method)}>
          {method.leadInLabel}
        </a>
      </li>
    );
  }

  /**
   * Render the list of methods that can be chosen
   *
   * @return {HTMLElement}
   */
  renderMethodList() {
    const { methods } = this.props;

    return (
      <ul>
        { methods.map(this.renderMethod.bind(this)) }
      </ul>
    );
  }

  render() {
    const { ss: { i18n } } = window;

    return (
      <div className="mfa-login-select-method">
        <h2>{i18n._t('MFALogin.OTHER_METHODS_TITLE', 'Try another way to verify')}</h2>
        { this.renderMethodList() }
        { this.renderLastResortMessage() }
        { this.renderControls() }
      </div>
    );
  }
}

SelectMethod.propTypes = {
  methods: PropTypes.arrayOf(registeredMethodType),
  onSelectMethod: PropTypes.func,
  onClickBack: PropTypes.func,
};

export default SelectMethod;
