/* global jest, describe, it, expect */

jest.mock('lib/Injector');

// eslint-disable-next-line no-unused-vars
import fetch from 'isomorphic-fetch';
import React from 'react';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import Register from '../Register';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line

Enzyme.configure({ adapter: new Adapter() });

window.ss = {
  i18n: { _t: (key, string) => string },
};

const endpoints = {
  register: '/fake/{urlSegment}',
};

const firstMethod = {
  urlSegment: 'aye',
  name: 'Aye',
  description: 'Register using aye',
  supportLink: 'https://google.com',
  component: 'Test',
};
const mockAvailableMethods = [
  firstMethod,
  {
    urlSegment: 'bee',
    name: 'Bee',
    description: 'Register using bee',
    supportLink: '',
    component: 'Test',
  },
];

const fetchMock = jest.spyOn(global, 'fetch');

describe('Register', () => {
  beforeEach(() => {
    fetchMock.mockImplementation(() => Promise.resolve({
      status: 200,
      json: () => Promise.resolve({}),
    }));
    fetchMock.mockClear();
    loadComponent.mockClear();
  });

  it('shows available methods to register', () => {
    const wrapper = shallow(
      <Register
        endpoints={endpoints}
        availableMethods={mockAvailableMethods}
      />
    );

    const listItems = wrapper.find('li');
    expect(listItems).toHaveLength(2);
    expect(listItems.first().text()).toMatch(/Register using aye/);
    expect(listItems.last().text()).toMatch(/Register using bee/);
  });

  it('shows links iff they are given', () => {
    const wrapper = shallow(
      <Register
        endpoints={endpoints}
        availableMethods={mockAvailableMethods}
      />
    );

    const listItems = wrapper.find('li');
    expect(listItems).toHaveLength(2);
    expect(listItems.first().find('a')).toHaveLength(1);
    expect(listItems.last().find('a')).toHaveLength(0);
  });

  it('will call the "start" endpoint when a method is chosen', done => {
    const wrapper = shallow(
      <Register
        endpoints={endpoints}
        availableMethods={mockAvailableMethods}
      />
    );

    // Choose the first method
    wrapper.find('li').first().find('button').simulate('click');

    setTimeout(() => {
      expect(fetchMock.mock.calls).toHaveLength(1);
      expect(fetchMock.mock.calls[0]).toEqual(['/fake/aye']);
      done();
    });
  });

  it('will load the component for the chosen method', done => {
    const wrapper = shallow(
      <Register
        endpoints={endpoints}
        availableMethods={mockAvailableMethods}
      />
    );

    // Choose the first method
    wrapper.find('li').first().find('button').simulate('click');

    setTimeout(() => {
      expect(wrapper.find('Test')).toHaveLength(1);
      done();
    });
  });

  it('forwards API response as props to injected component', (done) => {
    fetchMock.mockImplementation(() => Promise.resolve({
      json: () => Promise.resolve({
        myProp: 1,
        anotherProp: 'two',
      }),
    }));

    const wrapper = shallow(
      <Register
        endpoints={endpoints}
        availableMethods={mockAvailableMethods}
      />
    );

    wrapper.find('li').first().find('button').simulate('click');

    setTimeout(() => {
      expect(wrapper.find('Test').props()).toEqual(expect.objectContaining({
        myProp: 1,
        anotherProp: 'two',
      }));
      done();
    });
  });

  it('provides the current method definition to the injected component', (done) => {
    const wrapper = shallow(
      <Register
        endpoints={endpoints}
        availableMethods={mockAvailableMethods}
      />
    );

    wrapper.find('li').first().find('button').simulate('click');

    setTimeout(() => {
      expect(wrapper.find('Test').props()).toEqual(expect.objectContaining({
        method: firstMethod,
      }));
      done();
    });
  });

  it('calls the API when the complete function is called', done => {
    const wrapper = shallow(
      <Register
        endpoints={endpoints}
        availableMethods={mockAvailableMethods}
      />
    );

    wrapper.find('li').first().find('button').simulate('click');

    setTimeout(() => {
      expect(fetchMock.mock.calls).toHaveLength(1);
      const completeFunction = wrapper.find('Test').prop('onCompleteRegistration');
      completeFunction({ test: 1 });
      expect(fetchMock.mock.calls).toHaveLength(2);
      expect(fetchMock.mock.calls[1]).toEqual(['/fake/aye', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: '{"test":1}',
      }]);
      done();
    });
  });
});
