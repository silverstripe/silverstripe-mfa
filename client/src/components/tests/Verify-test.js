/* global jest, describe, it, expect */

jest.mock('lib/Injector');

// eslint-disable-next-line no-unused-vars
import fetch from 'isomorphic-fetch';
import React from 'react';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import Verify from '../Verify';
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

describe('Login', () => {
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
      expect(wrapper.find('SelectMethod')).toHaveLength(1);
      done();
    });
  });

  it('shows a back button on the show other methods pane that takes you back', (done) => {
    const wrapper = shallow(
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods}
      />
    );
    const preventDefault = jest.fn();

    wrapper.setState({
      showOtherMethods: true,
    });

    setTimeout(() => {
      expect(wrapper.find('SelectMethod')).toHaveLength(1);
      const chooseMethodWrapper = wrapper.find('SelectMethod').shallow();

      const backButton = chooseMethodWrapper.find('.mfa-verify-select-method__back');
      expect(backButton).toHaveLength(1);

      backButton.simulate('click', { preventDefault });
      expect(preventDefault.mock.calls).toHaveLength(1);

      setTimeout(() => {
        expect(wrapper.find('SelectMethod')).toHaveLength(0);
        done();
      });
    });
  });

  it('will trigger a load of a different method when clicked in the other methods pane', (done) => {
    const wrapper = shallow(
      <Verify
        endpoints={endpoints}
        registeredMethods={mockRegisteredMethods}
      />
    );
    const preventDefault = jest.fn();

    wrapper.setState({
      showOtherMethods: true,
    });

    setTimeout(() => {
      expect(fetchMock.mock.calls).toHaveLength(1);
      const chooseMethodWrapper = wrapper.find('SelectMethod').shallow();

      const otherMethod = chooseMethodWrapper.find('li a');
      expect(otherMethod).toHaveLength(1);

      otherMethod.simulate('click', { preventDefault });
      expect(preventDefault.mock.calls).toHaveLength(1);
      expect(fetchMock.mock.calls).toHaveLength(2);

      expect(fetchMock.mock.calls).toEqual([['/fake/aye'], ['/fake/bee']]);

      setTimeout(() => {
        expect(wrapper.find('SelectMethod')).toHaveLength(0);
        expect(wrapper.find('h2').text()).toBe('Login with bee');
        done();
      });
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
});
