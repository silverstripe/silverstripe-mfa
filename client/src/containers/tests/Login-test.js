/* global jest, describe, it, expect */

jest.mock('lib/Injector');

// eslint-disable-next-line no-unused-vars
import fetch from 'isomorphic-fetch';
import React from 'react';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import LoadingError from 'components/LoadingError';
import { Component as Login } from '../Login';

Enzyme.configure({ adapter: new Adapter() });

window.ss = {
  i18n: { _t: (key, string) => string },
};

const fetchMock = jest.spyOn(global, 'fetch');

describe('Login', () => {
  beforeEach(() => {
    fetchMock.mockClear();
  });

  describe('componentDidMount()', () => {
    it('handles schema fetch errors', done => {
      fetchMock.mockImplementation(() => Promise.resolve({
        status: 500,
      }));

      const wrapper = shallow(
        <Login schemaURL="/foo" />
      );

      setTimeout(() => {
        expect(wrapper.instance().state.schemaLoaded).toBe(true);
        done();
      });
    });

    it('handles successful schema fetch', done => {
      fetchMock.mockImplementation(() => Promise.resolve({
        status: 200,
        json: () => Promise.resolve({
          schemaData: { allMethods: [] },
        }),
      }));

      const wrapper = shallow(
        <Login schemaURL="/foo" />
      );

      setTimeout(() => {
        expect(wrapper.instance().state.schema).toEqual({
          schemaData: { allMethods: [] },
        });
        done();
      });
    });
  });

  describe('render()', () => {
    it('renders an error screen', done => {
      const wrapper = shallow(
        <Login schemaURL="/foo" />,
        { disableLifecycleMethods: true }
      );

      wrapper.instance().setState({
        loading: true,
        schema: null,
        schemaLoaded: true,
      }, () => {
        expect(wrapper.find(LoadingError)).toHaveLength(1);
        done();
      });
    });
  });
});
