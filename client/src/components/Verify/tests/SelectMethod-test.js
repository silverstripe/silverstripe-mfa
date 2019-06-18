/* global jest, describe, it, expect */

// eslint-disable-next-line no-unused-vars
import React from 'react';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import { Component as SelectMethod } from '../SelectMethod';

Enzyme.configure({ adapter: new Adapter() });

window.ss = {
  i18n: { _t: (key, string) => string },
};

const mockRegisteredMethods = [
  {
    urlSegment: 'aye',
    leadInLabel: 'Login with aye',
    component: 'Test',
  },
  {
    urlSegment: 'bee',
    leadInLabel: 'Login with bee',
    component: 'Test',
  },
];

const mockClickHandler = jest.fn();
const mockSelectMethodHandler = jest.fn();

describe('Verify', () => {
  beforeEach(() => {
    mockClickHandler.mockClear();
    mockSelectMethodHandler.mockClear();
  });

  describe('renderControls()', () => {
    it('shows a back button that takes you back', () => {
      const wrapper = shallow(
        <SelectMethod
          isAvailable={() => true}
          onClickBack={mockClickHandler}
          onSelectMethod={mockSelectMethodHandler}
          getUnavailableMessage={() => ''}
          methods={mockRegisteredMethods}
        />
      );

      expect(wrapper.find('.mfa-verify-select-method__actions')).toHaveLength(1);
      expect(wrapper.find('.mfa-verify-select-method__back')).toHaveLength(1);

      wrapper.find('.mfa-verify-select-method__back').simulate('click');
      expect(mockClickHandler).toHaveBeenCalled();
    });
  });

  describe('renderMethod()', () => {
    it('shows methods as unavailable', () => {
      const wrapper = shallow(
        <SelectMethod
          isAvailable={() => false}
          onClickBack={mockClickHandler}
          onSelectMethod={mockSelectMethodHandler}
          getUnavailableMessage={() => 'Browser does not support it'}
          methods={mockRegisteredMethods}
        />
      );

      const wrapperText = wrapper.text();
      expect(wrapperText).toContain(mockRegisteredMethods[0].leadInLabel);
      expect(wrapperText).toContain('Browser does not support it');
    });

    it('shows methods as available', () => {
      const wrapper = shallow(
        <SelectMethod
          isAvailable={() => true}
          onClickBack={mockClickHandler}
          onSelectMethod={mockSelectMethodHandler}
          getUnavailableMessage={() => ''}
          methods={mockRegisteredMethods}
        />
      );

      expect(wrapper.text()).toContain(mockRegisteredMethods[0].leadInLabel);
      expect(wrapper.find('li a')).toHaveLength(2);
    });

    it('triggers click handler when clicking a method', () => {
      const wrapper = shallow(
        <SelectMethod
          isAvailable={() => true}
          onClickBack={mockClickHandler}
          onSelectMethod={mockSelectMethodHandler}
          getUnavailableMessage={() => ''}
          methods={mockRegisteredMethods}
        />
      );

      const firstMethod = wrapper.find('li a').first();
      firstMethod.simulate('click');
      expect(mockSelectMethodHandler).toHaveBeenCalled();
    });
  });

  describe('render()', () => {
    it('renders an image', () => {
      const wrapper = shallow(
        <SelectMethod
          isAvailable={() => true}
          onClickBack={mockClickHandler}
          onSelectMethod={mockSelectMethodHandler}
          getUnavailableMessage={() => ''}
          methods={mockRegisteredMethods}
          resources={{
            more_options_image_url: '/foo.jpg',
          }}
        />
      );

      expect(wrapper.find('.mfa-verify-select-method__image')).toHaveLength(1);
    });
  });
});
