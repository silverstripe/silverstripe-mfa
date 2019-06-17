import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import registeredMethodType from 'types/registeredMethod';
import withMethodAvailability from 'state/methodAvailability/withMethodAvailability';

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
      <div className="mfa-verify-select-method__actions">
        <a href="#" className="mfa-verify-select-method__back" onClick={onClickBack}>
          {i18n._t('MFAVerify.BACK', 'Back')}
        </a>
      </div>
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
          'MFAVerify.LAST_RESORT_MESSAGE',
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

    // Unavailable state, e.g. if unsupported in the current browser
    if (!this.props.isAvailable(method)) {
      return (
        <li key={method.urlSegment}>
          {/* todo implement designs for this */}
          {method.leadInLabel} ({this.props.getUnavailableMessage(method)})
        </li>
      );
    }

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
      <ul className="mfa-verify-select-method__method-list">
        { methods.map(this.renderMethod.bind(this)) }
      </ul>
    );
  }

  render() {
    const { ss: { i18n } } = window;
    const { resources } = this.props;

    return (
      <div className="mfa-verify-select-method">
        <h2 className="mfa-section-title">
          {i18n._t('MFAVerify.OTHER_METHODS_TITLE', 'Try another way to verify')}
        </h2>
        <div className="mfa-verify-select-method__container">
          <div className="mfa-verify-select-method__content">
            { this.renderMethodList() }
            { this.renderLastResortMessage() }
            { this.renderControls() }
          </div>
          {
            resources && resources.more_options_image_url && <img
              alt={i18n._t('MultiFactorAuthentication.MORE_OPTIONS_IMAGE_ALT', 'Graphic depicting various MFA options')}
              aria-hidden="true"
              className="mfa-verify-select-method__image"
              src={resources.more_options_image_url}
            />
          }
        </div>
      </div>
    );
  }
}

SelectMethod.propTypes = {
  methods: PropTypes.arrayOf(registeredMethodType),
  getUnavailableMessage: PropTypes.func.isRequired,
  isAvailable: PropTypes.func.isRequired,
  onSelectMethod: PropTypes.func,
  onClickBack: PropTypes.func,
  resources: PropTypes.object,
};

export { SelectMethod as Component };

export default withMethodAvailability(SelectMethod);
