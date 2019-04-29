/* global jest, describe, it, expect */

import React from 'react';
import Enzyme, { render, shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import RegisteredMFAMethodListField from '../RegisteredMFAMethodListField';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line

Enzyme.configure({ adapter: new Adapter() });

window.ss = {
  i18n: { _t: (key, string) => string },
};

const altMethod = { name: 'Method', urlSegment: 'method', leadInLabel: '', component: '' };
const backupMethod = { ...altMethod, name: 'Backup Method', urlSegment: 'backup' };
const defaultMethod = { ...altMethod, name: 'Default Method', urlSegment: 'default' };

describe('RegisteredMFAMethodListField', () => {
  describe('baseMethods()', () => {
    it('filters out default and backup methods', () => {
      const registeredMethods = [altMethod, backupMethod, defaultMethod];

      const field = shallow(
        <RegisteredMFAMethodListField
          backupMethod={backupMethod}
          defaultMethod={defaultMethod}
          registeredMethods={registeredMethods}
        />
      );

      expect(field.instance().baseMethods()).toHaveLength(1);
    });
  });

  describe('render()', () => {
    it('renders the read-only view when readOnly is passed', () => {
      const registeredMethods = [altMethod];

      const field = render(
        <RegisteredMFAMethodListField readOnly registeredMethods={registeredMethods} />
      );

      expect(field.hasClass('registered-mfa-method-list-field--read-only')).toEqual(true);
    });
  });
});
