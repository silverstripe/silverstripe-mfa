import PropTypes from 'prop-types';
import React, { Component } from 'react';

import methodShape from '../../../types/registeredMethod';
import MethodListItem from './MethodListItem';

const fallbacks = require('../../../../lang/src/en.json');

class RegisteredMFAMethodListField extends Component {
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

  render() {
    const { ss: { i18n } } = window;
    const { defaultMethod } = this.props;

    const tDefault = i18n._t(
      'MultiFactorAuthentication.DEFAULT',
      fallbacks['MultiFactorAuthentication.DEFAULT']
    );

    return (
      <div className="registered-mfa-method-list-field registered-mfa-method-list-field--read-only">
        <ul className="method-list">
          { !defaultMethod && this.baseMethods().length < 1 && <li>No methods registered</li> }
          { defaultMethod && (<MethodListItem method={defaultMethod} suffix={`(${tDefault})`} />) }
          { this.renderBaseMethods() }
        </ul>
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

export default RegisteredMFAMethodListField;
