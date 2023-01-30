// Manages rendering RegisteredMFAMethodListFields in lieu of React support in ModelAdmin
import React from 'react';
import { createRoot } from 'react-dom/client';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line

const FIELD_SELECTOR = '.js-injector-boot [data-field-type="registered-mfa-method-list-field"]';

window.jQuery.entwine('ss', ($) => {
  $(FIELD_SELECTOR).entwine({
    ReactRoot: null,

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

      let root = this.getReactRoot();
      if (!root) {
        root = createRoot(this[0]);
        this.setReactRoot(root);
      }
      root.render(
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
        />
      );
    },

    onunmatch() {
      const root = this.getReactRoot();
      if (root) {
        root.unmount();
        this.setReactRoot(null);
      }
    }
  });
});
