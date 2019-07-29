/* global jest, describe, it, expect */

// eslint-disable-next-line no-unused-vars
import fetch from 'isomorphic-fetch';
import React from 'react';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import { Component as Introduction, ActionList } from '../Introduction';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line

Enzyme.configure({ adapter: new Adapter() });

window.ss = {
  i18n: { _t: (key, string) => string },
};

const fetchMock = jest.spyOn(global, 'fetch');
const handleContinueMock = jest.fn(() => true);
const handleSkipMock = jest.fn(() => true);

describe('Introduction', () => {
  beforeEach(() => {
    fetchMock.mockImplementation(() => Promise.resolve({
      status: 200,
      json: () => Promise.resolve({}),
    }));

    fetchMock.mockClear();
    handleContinueMock.mockClear();
    handleSkipMock.mockClear();
  });

  describe('render()', () => {
    it('renders images when resource URLs are supplied', () => {
      const wrapper = shallow(
        <Introduction
          onContinue={handleContinueMock}
          resources={{
            extra_factor_image_url: '#',
            unique_image_url: '#',
          }}
        />
      );

      const images = wrapper.find('img');

      expect(images).toHaveLength(2);
    });

    it('renders "find out more" link when user docs URL is supplied', () => {
      const wrapper = shallow(
        <Introduction
          onContinue={handleContinueMock}
          resources={{
            user_help_link: '#',
          }}
        />
      );

      const images = wrapper.find('a');

      expect(images).toHaveLength(1);
    });
  });

  describe('ActionList', () => {
    it('does not render a skip button by default', () => {
      const wrapper = shallow(
        <ActionList
          onContinue={handleContinueMock}
        />
      );

      const actionList = wrapper.find('button');

      expect(actionList).toHaveLength(1);
    });

    it('triggers the continue handler when the continue action is clicked', () => {
      const wrapper = shallow(
        <ActionList
          onContinue={handleContinueMock}
        />
      );

      wrapper.find('button').first().simulate('click');

      expect(handleContinueMock.mock.calls.length).toBe(1);
    });

    it('renders a skip button when supplied', () => {
      const wrapper = shallow(
        <ActionList
          canSkip
          onContinue={handleContinueMock}
          onSkip={handleContinueMock}
        />
      );

      const actionList = wrapper.find('button');

      expect(actionList).toHaveLength(2);
    });

    it('triggers the skip handler when the skip action is clicked', () => {
      const wrapper = shallow(
        <ActionList
          canSkip
          onContinue={handleContinueMock}
          onSkip={handleSkipMock}
        />
      );

      wrapper.find('button').last().simulate('click');

      expect(handleSkipMock.mock.calls.length).toBe(1);
    });
  });
});
