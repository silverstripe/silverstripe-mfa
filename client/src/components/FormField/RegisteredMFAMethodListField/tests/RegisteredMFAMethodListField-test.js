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
const defaultMethodName = 'default';

const RegisterComponent = () => <div />;
const onUpdateAvailableMethods = jest.fn();
const onSetRegisteredMethods = jest.fn();
const onSetDefaultMethod = jest.fn();

const translationStrings = require('../../../../../lang/src/en.json');

describe('RegisteredMFAMethodListField', () => {
  describe('baseMethods()', () => {
    it('filters out backup methods', () => {
      const registeredMethods = [altMethod, backupMethod, defaultMethod];

      const field = shallow(
        <RegisteredMFAMethodListField
          backupMethod={backupMethod}
          defaultMethod={defaultMethodName}
          registeredMethods={registeredMethods}
          RegisterComponent={RegisterComponent}
          onUpdateAvailableMethods={onUpdateAvailableMethods}
          onSetRegisteredMethods={onSetRegisteredMethods}
          onSetDefaultMethod={onSetDefaultMethod}
        />
      );

      expect(field.instance().getBaseMethods()).toHaveLength(2);
    });
  });

  describe('renderAddButton', () => {
    it('renders a button', () => {
      const availableMethods = [altMethod];

      const wrapper = shallow(
        <RegisteredMFAMethodListField
          backupMethod={backupMethod}
          defaultMethod={defaultMethodName}
          availableMethods={availableMethods}
          registeredMethods={[]}
          RegisterComponent={RegisterComponent}
          onUpdateAvailableMethods={onUpdateAvailableMethods}
          onSetRegisteredMethods={onSetRegisteredMethods}
          onSetDefaultMethod={onSetDefaultMethod}
        />
      );

      expect(wrapper.find('.registered-mfa-method-list-field__button')).toHaveLength(1);
    });

    it('doesn\'t render a button in read-only mode', () => {
      const availableMethods = [altMethod];

      const wrapper = shallow(
        <RegisteredMFAMethodListField
          backupMethod={backupMethod}
          defaultMethod={defaultMethodName}
          availableMethods={availableMethods}
          registeredMethods={[]}
          readOnly
          RegisterComponent={RegisterComponent}
          onUpdateAvailableMethods={onUpdateAvailableMethods}
          onSetRegisteredMethods={onSetRegisteredMethods}
          onSetDefaultMethod={onSetDefaultMethod}
        />
      );

      expect(wrapper.find('.registered-mfa-method-list-field__button')).toHaveLength(0);
    });


    it('provides a contextual message depending on registered methods', () => {
      const availableMethods = [altMethod];

      const withoutRegisteredMethods = shallow(
        <RegisteredMFAMethodListField
          backupMethod={backupMethod}
          defaultMethod={defaultMethodName}
          availableMethods={availableMethods}
          registeredMethods={[]}
          RegisterComponent={RegisterComponent}
          onUpdateAvailableMethods={onUpdateAvailableMethods}
          onSetRegisteredMethods={onSetRegisteredMethods}
          onSetDefaultMethod={onSetDefaultMethod}
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
          defaultMethod={defaultMethodName}
          availableMethods={availableMethods}
          registeredMethods={[defaultMethod]}
          RegisterComponent={RegisterComponent}
          onUpdateAvailableMethods={onUpdateAvailableMethods}
          onSetRegisteredMethods={onSetRegisteredMethods}
          onSetDefaultMethod={onSetDefaultMethod}
        />
      );

      expect(withRegisteredMethods
        .find('.registered-mfa-method-list-field__button')
        .shallow()
        .text()
      ).toBe(translationStrings['MultiFactorAuthentication.ADD_ANOTHER_METHOD']);
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
          onUpdateAvailableMethods={onUpdateAvailableMethods}
          onSetRegisteredMethods={onSetRegisteredMethods}
          onSetDefaultMethod={onSetDefaultMethod}
        />
      );


      expect(field.hasClass('registered-mfa-method-list-field--read-only')).toEqual(true);
    });

    it('renders a button when there are registerable methods', () => {
      const availableMethods = [altMethod];

      const withAvailableMethods = shallow(
        <RegisteredMFAMethodListField
          backupMethod={backupMethod}
          defaultMethod={defaultMethodName}
          availableMethods={availableMethods}
          registeredMethods={[]}
          RegisterComponent={RegisterComponent}
          onUpdateAvailableMethods={onUpdateAvailableMethods}
          onSetRegisteredMethods={onSetRegisteredMethods}
          onSetDefaultMethod={onSetDefaultMethod}
        />
      );

      expect(withAvailableMethods.find('.registered-mfa-method-list-field__button'))
        .toHaveLength(1);

      const withoutAvailableMethods = shallow(
        <RegisteredMFAMethodListField
          backupMethod={backupMethod}
          defaultMethod={defaultMethodName}
          registeredMethods={[]}
          RegisterComponent={RegisterComponent}
          onUpdateAvailableMethods={onUpdateAvailableMethods}
          onSetRegisteredMethods={onSetRegisteredMethods}
          onSetDefaultMethod={onSetDefaultMethod}
        />
      );

      expect(withoutAvailableMethods.find('.registered-mfa-method-list-field__button'))
        .toHaveLength(0);
    });
  });
});
