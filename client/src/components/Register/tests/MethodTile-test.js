/* global jest, describe, it, expect */

import React from 'react';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import MethodTile from '../MethodTile';

Enzyme.configure({ adapter: new Adapter() });

window.ss = {
  i18n: { _t: (key, string) => string },
};

const firstMethod = {
  urlSegment: 'aye',
  name: 'Aye',
  description: 'Register using aye',
  supportLink: 'https://google.com',
  component: 'Test',
  isAvailable: true,
};

const clickHandlerMock = jest.fn();

describe('MethodTile', () => {
  beforeEach(() => {
    clickHandlerMock.mockClear();
  });

  describe('handleClick()', () => {
    it('passes click to handler prop if method is available', () => {
      firstMethod.isAvailable = true;
      const wrapper = shallow(
        <MethodTile
          method={firstMethod}
          onClick={clickHandlerMock}
        />
      );
      wrapper.instance().handleClick({});
      expect(clickHandlerMock.mock.calls).toHaveLength(1);
    });

    it('doesn\'t do anything when method is not available', () => {
      firstMethod.isAvailable = false;
      const wrapper = shallow(
        <MethodTile
          method={firstMethod}
          onClick={clickHandlerMock}
        />
      );
      wrapper.instance().handleClick({});
      expect(clickHandlerMock.mock.calls).toHaveLength(0);
    });
  });

  describe('renderUnavailableMask()', () => {
    it('does nothing when the method is available', () => {
      firstMethod.isAvailable = true;
      const wrapper = shallow(
        <MethodTile
          method={firstMethod}
          onClick={clickHandlerMock}
        />
      );
      expect(wrapper.find('.mfa-method-tile__unavailable-mask')).toHaveLength(0);
    });

    it('renders a mask with message via props when unavailable', () => {
      firstMethod.isAvailable = false;
      firstMethod.unavailableMessage = 'Test message here';
      const wrapper = shallow(
        <MethodTile
          method={firstMethod}
          onClick={clickHandlerMock}
        />
      );
      const mask = wrapper.find('.mfa-method-tile__unavailable-mask');
      expect(mask).toHaveLength(1);
      expect(mask.text()).toContain('Test message here');
    });
  });

  describe('render()', () => {
    it('has a clickable interface', () => {
      firstMethod.isAvailable = true;
      const wrapper = shallow(
        <MethodTile
          method={firstMethod}
          onClick={clickHandlerMock}
        />
      );
      wrapper.find('.mfa-method-tile__content').simulate('click');
      expect(clickHandlerMock.mock.calls).toHaveLength(1);
    });

    it('treats the enter key as a click', () => {
      firstMethod.isAvailable = true;
      const wrapper = shallow(
        <MethodTile
          method={firstMethod}
          onClick={clickHandlerMock}
        />
      );
      wrapper.find('.mfa-method-tile__content').simulate('keyUp', { keyCode: 13 });
      expect(clickHandlerMock.mock.calls).toHaveLength(1);
    });

    it('attaches an active state when active', () => {
      const wrapper = shallow(
        <MethodTile
          isActive
          method={firstMethod}
          onClick={clickHandlerMock}
        />
      );
      expect(wrapper.find('.mfa-method-tile--active')).toHaveLength(1);
    });
  });
});
