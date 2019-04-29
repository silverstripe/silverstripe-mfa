import PropTypes from 'prop-types';
import React, { Component } from 'react';

import methodShape from '../../../types/registeredMethod';
import ReadOnlyView from './ReadOnlyView';

class RegisteredMFAMethodListField extends Component {
  /**
   * The backup and default methods are rendered separately
   * @returns {*}
   */
  baseMethods() {
    const { backupMethod, defaultMethod } = this.props;
    let methods = this.props.registeredMethods;

    if (backupMethod) {
      methods = methods.filter(rM => rM.urlSegment !== backupMethod.urlSegment);
    }

    if (defaultMethod) {
      methods = methods.filter(rM => rM.urlSegment !== defaultMethod.urlSegment);
    }

    return methods;
  }

  render() {
    const { defaultMethod, readOnly } = this.props;

    if (readOnly) {
      return (
        <ReadOnlyView
          defaultMethod={defaultMethod}
          methods={this.baseMethods()}
        />
      );
    }

    return (<h1>(EditableView)</h1>);
  }
}

RegisteredMFAMethodListField.propTypes = {
  backupMethod: methodShape,
  defaultMethod: methodShape,
  readOnly: PropTypes.bool,
  registeredMethods: PropTypes.arrayOf(methodShape).isRequired,
};

export default RegisteredMFAMethodListField;
