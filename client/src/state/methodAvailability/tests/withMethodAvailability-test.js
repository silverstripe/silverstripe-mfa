/* global jest, describe, it, expect */

// eslint-disable-next-line no-unused-vars
import React from 'react';
import Enzyme, { shallow, mount } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import withMethodAvailability, { hoc } from '../withMethodAvailbility';
import { createStore } from 'redux';
import { Provider } from 'react-redux';

Enzyme.configure({ adapter: new Adapter() });

window.ss = {
  i18n: { _t: (key, string) => string },
};

const firstMethod = {
  isAvailable: true,
  urlSegment: 'web-authn',
  leadInLabel: 'WebAuthn',
  component: 'Test',
  unavailableMessage: 'Hello universe',
};
const secondMethod = {
  isAvailable: true,
  urlSegment: 'totp',
  leadInLabel: 'TOTP',
  component: 'Test',
  unavailableMessage: 'Over the totp',
};

const BaseComponent = () => <div />;

describe('withMethodAvailability()', () => {
  it('identifies overrides from the Redux store and connects them as props', () => {
    const WrappedComponent = withMethodAvailability(BaseComponent);
    const initialState = {
      mfaRegister: {
        availableMethods: [firstMethod],
      },
      mfaVerify: {
        allMethods: [firstMethod, secondMethod],
      },
      'web-authnAvailability': {
        isAvailable: false,
        unavailableMessage: 'Because it is a test',
      },
    };
    const store = createStore(() => initialState);

    const wrapper = mount(
      <Provider store={store}>
        <WrappedComponent />
      </Provider>
    );

    expect(wrapper.find('BaseComponent')).toHaveLength(1);
    expect(wrapper.find('BaseComponent').props().availableMethodOverrides['web-authn'].isAvailable).toBe(false);
  });

  describe('getAvailabilityOverride()', () => {
    it('returns empty with method does not have an override', () => {
      const WrappedComponent = hoc(BaseComponent);
      const wrapper = shallow(
        <WrappedComponent
          availableMethodOverrides={[
            {
              'web-authn': {
                isAvailable: true,
              }
            }
          ]}
        />
      );

      expect(wrapper.instance().getAvailabilityOverride(firstMethod)).toEqual({});
    });

    it('returns method override when isAvailable is true', () => {
      const WrappedComponent = hoc(BaseComponent);
      const wrapper = shallow(
        <WrappedComponent
          availableMethodOverrides={{
            'web-authn': {
              isAvailable: true,
            }
          }}
        />
      );

      expect(wrapper.instance().getAvailabilityOverride(firstMethod).isAvailable).toBe(true);
    });

    it('returns method override when isAvailable is false, or falls back to method from props', () => {
      const WrappedComponent = hoc(BaseComponent);
      const wrapper = shallow(
        <WrappedComponent
          method={secondMethod}
          availableMethodOverrides={{
            'web-authn': {
              isAvailable: false,
            }
          }}
        />
      );

      // An available override
      expect(wrapper.instance().getAvailabilityOverride(firstMethod).isAvailable).toBe(false);
      // From props
      expect(wrapper.instance().getAvailabilityOverride()).toEqual({});
    });
  });

  describe('getUnavailableMessage()', () => {
    it('returns a message when an override exists, or falls back to method from props', () => {
      const WrappedComponent = hoc(BaseComponent);
      const wrapper = shallow(
        <WrappedComponent
          availableMethodOverrides={{
            'web-authn': {
              isAvailable: false,
              unavailableMessage: 'Hello world'
            },
          }}
        />
      );

      // An available override
      expect(wrapper.instance().getUnavailableMessage(firstMethod)).toBe('Hello world');
      // From method in props
      expect(wrapper.instance().getUnavailableMessage(secondMethod)).toBe('Over the totp');
    });
  });

  describe('isAvailable()', () => {
    it('returns a message when an override exists, or falls back to method from props', () => {
      const WrappedComponent = hoc(BaseComponent);
      const wrapper = shallow(
        <WrappedComponent
          method={{
            urlSegment: 'fake',
            isAvailable: false,
          }}
          availableMethodOverrides={{
            'web-authn': {
              isAvailable: false,
              unavailableMessage: 'Hello world'
            },
          }}
        />
      );

      // An available override, provided by argument
      expect(wrapper.instance().isAvailable(firstMethod)).toBe(false);
      // From method in props, provided by argument
      expect(wrapper.instance().isAvailable(secondMethod)).toBe(true);
      // Using method from props
      expect(wrapper.instance().isAvailable()).toBe(false);
    });
  });

  describe('render()', () => {
    it('renders the wrapped component', () => {
      const WrappedComponent = hoc(BaseComponent);
      const wrapper = shallow(
        <WrappedComponent />
      );

      expect(wrapper.find(BaseComponent)).toHaveLength(1);
    });

    it('passes its props to the wrapped component', () => {
      const WrappedComponent = hoc(BaseComponent);
      const wrapper = shallow(
        <WrappedComponent foo="bar" />
      );

      expect(wrapper.find(BaseComponent).props().foo).toBe('bar');
    });

    it('provides callbacks for isAvailable and getUnavailableMessage', () => {
      const WrappedComponent = hoc(BaseComponent);
      const wrapper = shallow(
        <WrappedComponent />
      );

      expect(wrapper.find(BaseComponent).props().isAvailable).not.toBeUndefined();
      expect(wrapper.find(BaseComponent).props().getUnavailableMessage).not.toBeUndefined();
    });
  });
});
