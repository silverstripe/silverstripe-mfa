import React from 'react';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import registeredMethodType from 'types/registeredMethod';
import withMethodAvailability from 'state/methodAvailability/withMethodAvailability';

function SelectMethod(props) {
  const {
    isAvailable,
    getUnavailableMessage,
    methods,
    onClickBack,
    onSelectMethod,
    resources,
  } = props;
  const i18n = window.ss.i18n;

  /**
   * Returns controls that are rendered at the bottom of the panel (eg. the back button)
   */
  function renderControls() {
    return <div className="mfa-verify-select-method__actions">
      <a href="#" className="mfa-verify-select-method__back" onClick={onClickBack}>
        {i18n._t('MFAVerify.BACK', 'Back')}
      </a>
    </div>;
  }

  /**
   * Render the "last resort" message informing users what to do if they cannot use any given option
   */
  function renderLastResortMessage() {
    return <p>
      {i18n._t(
        'MFAVerify.LAST_RESORT_MESSAGE',
        'Contact your site administrator if you require your multi-factor authentication to ' +
        'be reset.'
      )}
    </p>;
  }

  /**
   * Render a list item for the given method
   */
  function renderMethod(method) {
    const leadInLabel = i18n.inject(i18n._t('MFAVerify.VERIFY_WITH', 'Verify with {method}'), {
      method: method.name.toLowerCase(),
    });
    // Unavailable state, e.g. if unsupported in the current browser
    if (!isAvailable(method)) {
      const helpLink = method.supportLink && (
        <span>
          (<a href={method.supportLink} target="_blank" rel="noreferrer noopener">{
            i18n._t('MFASelectMethod.UNAVAILABLE', 'unavailable')
          }</a>)
        </span>
      );
      const message = getUnavailableMessage(method);
      const className = classnames(
        'mfa-verify-select-method__method',
        'mfa-verify-select-method__method--unavailable'
      );

      return <li key={method.urlSegment} className={className}>
        {leadInLabel} {helpLink}
        {message && <span className="mfa-verify-select-method__method-message">{message}</span>}
      </li>;
    }
    return <li key={method.urlSegment} className="mfa-verify-select-method__method">
      <a href="#" onClick={onSelectMethod(method)}>
        {leadInLabel}
      </a>
    </li>;
  }

  /**
   * Render the list of methods that can be chosen
   */
  function renderMethodList() {
    return <ul className="mfa-verify-select-method__method-list">
      { methods.map(renderMethod) }
    </ul>;
  }

  // Render the component
  return <div className="mfa-verify-select-method">
    <h2 className="mfa-section-title">
      {i18n._t('MFAVerify.OTHER_METHODS_TITLE', 'Try another way to verify')}
    </h2>
    <div className="mfa-verify-select-method__container">
      <div className="mfa-verify-select-method__content">
        { renderMethodList() }
        { renderLastResortMessage() }
        { renderControls() }
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
  </div>;
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
