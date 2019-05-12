// Manages rendering RegisteredMFAMethodListFields in lieu of React support in ModelAdmin
import React from 'react';
import ReactDOM from 'react-dom';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line

const FIELD_SELECTOR = '.js-injector-boot [data-field-type="registered-mfa-method-list-field"]';

window.jQuery.entwine('ss', ($) => {
  $(FIELD_SELECTOR).entwine({
    onmatch() {
      const RegisteredMFAMethodListField = loadComponent('RegisteredMFAMethodListField');
      const schemaData = this.data('schema');

      ReactDOM.render(
        <RegisteredMFAMethodListField
          backupMethod={schemaData.schema.backupMethod}
          defaultMethod={schemaData.schema.defaultMethod}
          readOnly={schemaData.readOnly}
          registeredMethods={schemaData.schema.registeredMethods}
          availableMethods={schemaData.schema.availableMethods}
          resources={schemaData.schema.resources}
        />,
        this[0]
      );
    }
  });
});
