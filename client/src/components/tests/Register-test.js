/* global jest, describe, it, expect */

jest.mock('lib/Injector');
jest.mock('../Register/SelectMethod');

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
  skip: '/fake/skip',
  complete: '/fake/complete',
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

  it('renders the Introduction UI on first load', () => {
    const wrapper = shallow(
      <Register
        canSkip
        endpoints={endpoints}
        availableMethods={mockAvailableMethods}
        resources={{}}
      />
    );

    const actionList = wrapper.find('Introduction');
    expect(actionList).toHaveLength(1);
  });

  describe('handleCompleteRegistration()', () => {
    it('will call the "start" endpoint when a method is chosen', done => {
      const wrapper = shallow(
        <Register
          endpoints={endpoints}
          availableMethods={mockAvailableMethods}
          resources={{}}
        />,
        { disableLifecycleMethods: true }
      );
      wrapper.setState({ isStarted: true, selectedMethod: firstMethod });
      wrapper.instance().handleCompleteRegistration({ myData: 'foo' });

      setTimeout(() => {
        expect(fetchMock.mock.calls).toHaveLength(1);
        const firstCallJson = JSON.stringify(fetchMock.mock.calls[0]);
        expect(firstCallJson).toContain('/fake/aye');
        expect(firstCallJson).toContain('myData');
        expect(firstCallJson).toContain('foo');
        done();
      });
    });
  });

  describe('renderMethod()', () => {
    it('will load the component for the chosen method', done => {
      const wrapper = shallow(
        <Register
          endpoints={endpoints}
          availableMethods={mockAvailableMethods}
          resources={{}}
        />
      );

      // Choose the first method
      wrapper.setState({ isStarted: true, selectedMethod: firstMethod });

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
          resources={{}}
        />
      );

      wrapper.setState({ isStarted: true, selectedMethod: firstMethod });

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
          resources={{}}
        />
      );

      wrapper.setState({ isStarted: true, selectedMethod: firstMethod });

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
          resources={{}}
        />
      );

      wrapper.setState({ isStarted: true, selectedMethod: firstMethod });

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

  describe('renderOptions()', () => {
    it('renders a SelectMethod with available methods to register passed', () => {
      const wrapper = shallow(
        <Register
          endpoints={endpoints}
          availableMethods={mockAvailableMethods}
          resources={{}}
        />
      );

      wrapper.setState({ isStarted: true });

      const listItems = wrapper.find('SelectMethod');
      const methods = listItems.props().methods;

      expect(methods).toHaveLength(2);
      expect(methods[0].description).toMatch(/Register using aye/);
      expect(methods[1].description).toMatch(/Register using bee/);
    });
  });
});
