// Manages rendering RegisteredMFAMethodListFields in lieu of React support in ModelAdmin
import React from 'react';
import ReactDOM from 'react-dom';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line

const FIELD_SELECTOR = '.js-injector-boot [data-field-type="registered-mfa-method-list-field"]';

window.jQuery.entwine('ss', ($) => {
  $(FIELD_SELECTOR).entwine({
    onmatch() {
      const RegisteredMFAMethodListField = loadComponent('RegisteredMFAMethodListField');
      const { readOnly, schema: {
        backupMethod,
        defaultMethod,
        registeredMethods,
        availableMethods,
        resources,
        endpoints,
        resetEndpoint,
      } } = this.data('schema');

      ReactDOM.render(
        <RegisteredMFAMethodListField
          backupMethod={backupMethod}
          defaultMethod={defaultMethod}
          readOnly={readOnly}
          registeredMethods={registeredMethods}
          availableMethods={availableMethods}
          resources={resources}
          endpoints={endpoints}
          resetEndpoint={resetEndpoint}
        />,
        this[0]
      );
    }
  });
});
