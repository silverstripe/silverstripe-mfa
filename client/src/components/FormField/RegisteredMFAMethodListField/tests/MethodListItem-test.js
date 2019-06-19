/* global jest, describe, it, expect */
import React from 'react';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import MethodListItem from '../MethodListItem';

Enzyme.configure({ adapter: new Adapter() });

window.ss = {
  i18n: {
    _t: (key, string) => string,
    detectLocale: () => 'en_NZ',
    inject: (message) => message, // not a great mock...
  },
};

describe('MethodListitem', () => {
  describe('getStatusMessage()', () => {
    it('identifies default methods', () => {
      const wrapper = shallow(
        <MethodListItem
          method={{ urlSegment: 'foo', }}
          isDefaultMethod
        />
      );

      expect(wrapper.instance().getStatusMessage()).toContain('(default)');
    });

    it('identifies backup methods', () => {
      const wrapper = shallow(
        <MethodListItem
          method={{ urlSegment: 'foo', }}
          isBackupMethod
        />
      );

      expect(wrapper.instance().getStatusMessage()).toContain('Created');
    });
  });
});
