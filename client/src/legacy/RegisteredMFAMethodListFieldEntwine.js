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
        allAvailableMethods,
        resources,
        endpoints,
        backupCreatedDate,
        resetEndpoint,
        isMFARequired,
      } } = this.data('schema');

      ReactDOM.render(
        <RegisteredMFAMethodListField
          backupMethod={backupMethod}
          readOnly={readOnly}
          initialDefaultMethod={defaultMethod}
          initialRegisteredMethods={registeredMethods}
          initialAvailableMethods={availableMethods}
          allAvailableMethods={allAvailableMethods}
          resources={resources}
          endpoints={endpoints}
          backupCreatedDate={backupCreatedDate}
          resetEndpoint={resetEndpoint}
          isMFARequired={isMFARequired}
        />,
        this[0]
      );
    }
  });
});
