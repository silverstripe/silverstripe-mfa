/* global jest, describe, it, expect */

import React from 'react';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import { Component as RegisteredMFAMethodListField } from '../RegisteredMFAMethodListField';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line

Enzyme.configure({ adapter: new Adapter() });

window.ss = {
  i18n: { _t: (key, string) => string },
};

const altMethod = { name: 'Method', urlSegment: 'method', leadInLabel: '', component: '' };
const backupMethod = { ...altMethod, name: 'Backup Method', urlSegment: 'backup' };
const defaultMethod = { ...altMethod, name: 'Default Method', urlSegment: 'default' };

const RegisterComponent = () => <div />;

const translationStrings = require('../../../../../lang/src/en.json');

describe('RegisteredMFAMethodListField', () => {
  describe('baseMethods()', () => {
    it('filters out default and backup methods', () => {
      const registeredMethods = [altMethod, backupMethod, defaultMethod];

      const field = shallow(
        <RegisteredMFAMethodListField
          backupMethod={backupMethod}
          defaultMethod={defaultMethod}
          registeredMethods={registeredMethods}
          RegisterComponent={RegisterComponent}
        />
      );

      expect(field.instance().baseMethods()).toHaveLength(1);
    });
  });

  describe('render()', () => {
    it('renders the read-only view when readOnly is passed', () => {
      const registeredMethods = [altMethod];

      const field = shallow(
        <RegisteredMFAMethodListField
          readOnly
          registeredMethods={registeredMethods}
          RegisterComponent={RegisterComponent}
        />
      );


      expect(field.hasClass('registered-mfa-method-list-field--read-only')).toEqual(true);
    });

    describe('renders a button', () => {
      it('when there are registerable methods', () => {
        const availableMethods = [altMethod];

        const withAvailableMethods = shallow(
          <RegisteredMFAMethodListField
            backupMethod={backupMethod}
            defaultMethod={defaultMethod}
            availableMethods={availableMethods}
            registeredMethods={[]}
            RegisterComponent={RegisterComponent}
          />
        );

        expect(withAvailableMethods.find('.registered-mfa-method-list-field__button'))
          .toHaveLength(1);

        const withoutAvailableMethods = shallow(
          <RegisteredMFAMethodListField
            backupMethod={backupMethod}
            defaultMethod={defaultMethod}
            registeredMethods={[]}
            RegisterComponent={RegisterComponent}
          />
        );

        expect(withoutAvailableMethods.find('.registered-mfa-method-list-field__button'))
          .toHaveLength(0);
      });

      it('with a contextual message depending on registered methods', () => {
        const availableMethods = [altMethod];

        const withoutRegisteredMethods = shallow(
          <RegisteredMFAMethodListField
            backupMethod={backupMethod}
            defaultMethod={defaultMethod}
            availableMethods={availableMethods}
            registeredMethods={[]}
            RegisterComponent={RegisterComponent}
          />
        );

        expect(withoutRegisteredMethods
          .find('.registered-mfa-method-list-field__button')
          .shallow()
          .text()
        ).toBe(translationStrings['MultiFactorAuthentication.ADD_FIRST_METHOD']);

        const withRegisteredMethods = shallow(
          <RegisteredMFAMethodListField
            backupMethod={backupMethod}
            defaultMethod={defaultMethod}
            availableMethods={availableMethods}
            registeredMethods={[defaultMethod]}
            RegisterComponent={RegisterComponent}
          />
        );

        expect(withRegisteredMethods
          .find('.registered-mfa-method-list-field__button')
          .shallow()
          .text()
        ).toBe(translationStrings['MultiFactorAuthentication.ADD_ANOTHER_METHOD']);
      });
    });
  });
});
