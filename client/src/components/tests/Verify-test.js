/* global jest, describe, it, expect */

jest.mock('lib/Injector');

// eslint-disable-next-line no-unused-vars
import fetch from 'isomorphic-fetch';
import React from 'react';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import { Component as Verify } from '../Verify';
import SelectMethod from '../Verify/SelectMethod';
import LoadingError from 'components/LoadingError';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line

Enzyme.configure({ adapter: new Adapter() });

window.ss = {
  i18n: { _t: (key, string) => string },
};

const endpoints = {
  verify: '/fake/{urlSegment}',
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

const fetchMock = jest.spyOn(global, 'fetch');

describe('Verify', () => {
  beforeEach(() => {
    fetchMock.mockImplementation(() => Promise.resolve({
      status: 200,
      json: () => Promise.resolve({}),
    }));
    fetchMock.mockClear();
    loadComponent.mockClear();
  });

  it.skip('? if there are no registered methods', () => {
    // Currently it renders nothing but there's undefined behaviour here
    // TODO Update this test when there's defined behaviour
  });

  it('chooses a default method if none is given', () => {
    const wrapper = shallow(
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods}
      />
    );

    expect(wrapper.state('selectedMethod')).toEqual({
      urlSegment: 'aye',
      leadInLabel: 'Login with aye',
      component: 'Test',
    });
  });

  it('respects a given default method', () => {
    const wrapper = shallow(
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods}
        defaultMethod="bee"
      />
    );

    expect(wrapper.state('selectedMethod')).toEqual({
      urlSegment: 'bee',
      leadInLabel: 'Login with bee',
      component: 'Test',
    });
  });

  it('fetches schema from the given login endpoint', () => {
    shallow(
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods}
      />
    );

    expect(fetchMock.mock.calls).toHaveLength(1);
    expect(fetchMock.mock.calls[0]).toEqual(['/fake/aye']);
  });

  it('loads the default method component', (done) => {
    const wrapper = shallow(
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods}
      />
    );

    expect(loadComponent.mock.calls).toHaveLength(1);
    expect(loadComponent.mock.calls[0]).toEqual(['Test']);

    // Defer testing of final render state so that we don't inspect loading state
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
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods}
      />
    );

    // Defer testing of final render state so that we don't inspect loading state
    setTimeout(() => {
      expect(wrapper.find('Test').props()).toEqual(expect.objectContaining({
        myProp: 1,
        anotherProp: 'two',
      }));
      done();
    });
  });

  it('handles a csrf token provided with the props and adds it to state', (done) => {
    fetchMock.mockImplementation(() => Promise.resolve({
      json: () => Promise.resolve({
        SecurityID: 'test',
        myProp: 1,
        anotherProp: 'two',
      }),
    }));

    const wrapper = shallow(
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods}
      />
    );

    // Defer testing of final render state so that we don't inspect loading state
    setTimeout(() => {
      expect(wrapper.find('Test').props()).toEqual(expect.objectContaining({
        myProp: 1,
        anotherProp: 'two',
      }));
      expect(wrapper.state('token')).toBe('test');
      done();
    });
  });

  it('provides a control to choose other methods to the injected component', (done) => {
    const wrapper = shallow(
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods}
      />
    );

    // Defer testing of final render state so that we don't inspect loading state
    setTimeout(() => {
      expect(shallow(
        wrapper.find('Test').prop('moreOptionsControl')
      ).matchesElement(<a>More options</a>)).toBe(true);
      done();
    });
  });

  it('does not provides a control to choose other methods to the injected component when there are too few methods', (done) => {
    const wrapper = shallow(
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods.slice(0, 1)}
      />
    );

    // Defer testing of final render state so that we don't inspect loading state
    setTimeout(() => {
      expect(wrapper.find('Test').prop('moreOptionsControl')).toBeNull();
      done();
    });
  });

  it('handles a click event on the show other methods control', (done) => {
    const wrapper = shallow(
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods}
      />
    );

    // Defer testing of final render state so that we don't inspect loading state
    setTimeout(() => {
      const moreOptionsControl = shallow(wrapper.find('Test').prop('moreOptionsControl'));
      const preventDefault = jest.fn();

      moreOptionsControl.simulate('click', { preventDefault });

      expect(preventDefault.mock.calls).toHaveLength(1);
      expect(wrapper.state('showOtherMethods')).toBe(true);
      expect(wrapper.find(SelectMethod)).toHaveLength(1);
      done();
    });
  });

  it('will use the login endpoint to verify a completed login', (done) => {
    const onCompleteVerification = jest.fn();
    const wrapper = shallow(
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods}
        onCompleteVerification={onCompleteVerification}
      />
    );

    setTimeout(() => {
      expect(fetchMock.mock.calls).toHaveLength(1);
      const completionFunction = wrapper.find('Test').prop('onCompleteVerification');
      completionFunction({ test: 1 });
      expect(fetchMock.mock.calls).toHaveLength(2);
      expect(fetchMock.mock.calls[1]).toEqual(['/fake/aye', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: '{"test":1}',
      }]);
      setTimeout(() => {
        expect(onCompleteVerification.mock.calls).toHaveLength(1);
        done();
      });
    });
  });

  it('will add a token from state when calling the verify endpoint', (done) => {
    const onCompleteVerification = jest.fn();
    const wrapper = shallow(
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods}
        onCompleteVerification={onCompleteVerification}
      />
    );

    setTimeout(() => {
      expect(fetchMock.mock.calls).toHaveLength(1);
      wrapper.setState({
        token: 'test',
      });
      const completionFunction = wrapper.find('Test').prop('onCompleteVerification');
      completionFunction({ test: 1 });
      expect(fetchMock.mock.calls).toHaveLength(2);
      expect(fetchMock.mock.calls[1][0]).toEqual('/fake/aye?SecurityID=test');
      done();
    });
  });

  it('will provide a message from any unverified login to the injected component', done => {
    const onCompleteVerification = jest.fn();
    const wrapper = shallow(
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods}
        onCompleteVerification={onCompleteVerification}
      />
    );

    setTimeout(() => {
      expect(fetchMock.mock.calls).toHaveLength(1);

      fetchMock.mockImplementation(() => Promise.resolve({
        status: 400,
        json: () => Promise.resolve({
          message: 'It was a failure',
        }),
      }));
      fetchMock.mockClear();

      const completionFunction = wrapper.find('Test').prop('onCompleteVerification');
      completionFunction({ test: 1 });
      expect(fetchMock.mock.calls).toHaveLength(1);
      expect(fetchMock.mock.calls[0]).toEqual(['/fake/aye', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: '{"test":1}',
      }]);
      setTimeout(() => {
        expect(onCompleteVerification.mock.calls).toHaveLength(0);
        expect(wrapper.find('Test').props()).toEqual(expect.objectContaining({
          error: 'It was a failure',
        }));
        done();
      });
    });
  });

  describe('renderSelectedMethod()', () => {
    it('renders an unavailable screen when the selected method is unavailable', done => {
      const wrapper = shallow(
        <Verify
          endpoints={endpoints}
          registeredMethods={mockRegisteredMethods}
          isAvailable={() => false}
          getUnavailableMessage={() => 'There is no spoon'}
        />
      );

      // Defer testing of final render state so that we don't inspect loading state
      setTimeout(() => {
        // Enable a selected method
        wrapper.instance().setState({
          selectedMethod: mockRegisteredMethods[0],
        });

        expect(wrapper.find(LoadingError)).toHaveLength(1);
        done();
      });
    });
  });
});
