/* global jest, test, expect */

import React from 'react';
import { render } from '@testing-library/react';
import { hoc as withMethodAvailability } from '../withMethodAvailability';

// Mock component to test the HOC
const MockComponent = ({
  isAvailable,
  getUnavailableMessage,
  method,
  availableMethodOverrides,
  ...props
}) => (
  <div data-testid="mock-component">
    <span data-testid="available">{isAvailable() ? 'available' : 'unavailable'}</span>
    <span data-testid="message">{getUnavailableMessage()}</span>
    {props.children}
  </div>
);

function makeProps(obj = {}) {
  return {
    method: {
      urlSegment: 'test-method',
      isAvailable: true,
      unavailableMessage: '',
    },
    availableMethodOverrides: {},
    ...obj
  };
}

test('withMethodAvailability passes isAvailable and getUnavailableMessage as props', () => {
  const WrappedComponent = withMethodAvailability(MockComponent);
  const { getByTestId } = render(
    <WrappedComponent
      {...makeProps({
        method: {
          urlSegment: 'test-method',
          isAvailable: true,
          unavailableMessage: '',
        }
      })}
    />
  );
  const available = getByTestId('available');
  expect(available.textContent).toBe('available');
});

test('withMethodAvailability respects method isAvailable from props', () => {
  const WrappedComponent = withMethodAvailability(MockComponent);
  const { getByTestId } = render(
    <WrappedComponent
      {...makeProps({
        method: {
          urlSegment: 'test-method',
          isAvailable: false,
          unavailableMessage: 'Method not available',
        }
      })}
    />
  );
  const available = getByTestId('available');
  expect(available.textContent).toBe('unavailable');
});

test('withMethodAvailability returns unavailable message from method props', () => {
  const WrappedComponent = withMethodAvailability(MockComponent);
  const { getByTestId } = render(
    <WrappedComponent
      {...makeProps({
        method: {
          urlSegment: 'test-method',
          isAvailable: false,
          unavailableMessage: 'This method is not supported',
        }
      })}
    />
  );
  const message = getByTestId('message');
  expect(message.textContent).toBe('This method is not supported');
});

test('withMethodAvailability overrides backend availability with Redux override', () => {
  const WrappedComponent = withMethodAvailability(MockComponent);
  const { getByTestId } = render(
    <WrappedComponent
      {...makeProps({
        method: {
          urlSegment: 'webauthn',
          isAvailable: true,
          unavailableMessage: '',
        },
        availableMethodOverrides: {
          webauthn: {
            isAvailable: false,
          }
        }
      })}
    />
  );
  const available = getByTestId('available');
  expect(available.textContent).toBe('unavailable');
});

test('withMethodAvailability returns override unavailable message instead of backend message', () => {
  const WrappedComponent = withMethodAvailability(MockComponent);
  const { getByTestId } = render(
    <WrappedComponent
      {...makeProps({
        method: {
          urlSegment: 'webauthn',
          isAvailable: true,
          unavailableMessage: 'Backend message',
        },
        availableMethodOverrides: {
          webauthn: {
            isAvailable: false,
            unavailableMessage: 'Override message from frontend',
          }
        }
      })}
    />
  );
  const message = getByTestId('message');
  expect(message.textContent).toBe('Override message from frontend');
});

test('withMethodAvailability ignores overrides for methods without overrides defined', () => {
  const WrappedComponent = withMethodAvailability(MockComponent);
  const { getByTestId } = render(
    <WrappedComponent
      {...makeProps({
        method: {
          urlSegment: 'basic',
          isAvailable: false,
          unavailableMessage: 'Basic method unavailable',
        },
        availableMethodOverrides: {
          webauthn: {
            isAvailable: true,
          }
        }
      })}
    />
  );
  const message = getByTestId('message');
  expect(message.textContent).toBe('Basic method unavailable');
});

test('withMethodAvailability returns empty string for unavailable message when none provided', () => {
  const WrappedComponent = withMethodAvailability(MockComponent);
  const { getByTestId } = render(
    <WrappedComponent
      {...makeProps({
        method: {
          urlSegment: 'test-method',
          isAvailable: true,
          unavailableMessage: undefined,
        }
      })}
    />
  );
  const message = getByTestId('message');
  expect(message.textContent).toBe('');
});

test('withMethodAvailability forwards all other props to wrapped component', () => {
  const WrappedComponent = withMethodAvailability(MockComponent);
  const { container } = render(
    <WrappedComponent
      {...makeProps({
        className: 'custom-class',
        id: 'test-id',
      })}
    />
  );
  const component = container.querySelector('[data-testid="mock-component"]');
  expect(component).not.toBeNull();
});

test('withMethodAvailability sets correct displayName on wrapped component', () => {
  MockComponent.displayName = 'TestComponent';
  const WrappedComponent = withMethodAvailability(MockComponent);
  expect(WrappedComponent.displayName).toBe('WithMethodAvailability(TestComponent)');
});

test('withMethodAvailability sets displayName based on component name when displayName not provided', () => {
  const TestComponent = () => <div />;
  const WrappedComponent = withMethodAvailability(TestComponent);
  expect(WrappedComponent.displayName).toBe('WithMethodAvailability(TestComponent)');
});

test('withMethodAvailability handles multiple different method types', () => {
  const WrappedComponent = withMethodAvailability(MockComponent);
  const { rerender, getByTestId } = render(
    <WrappedComponent
      {...makeProps({
        method: {
          urlSegment: 'method1',
          isAvailable: true,
          unavailableMessage: 'Method 1 message',
        }
      })}
    />
  );

  rerender(
    <WrappedComponent
      {...makeProps({
        method: {
          urlSegment: 'method2',
          isAvailable: false,
          unavailableMessage: 'Method 2 message',
        },
        availableMethodOverrides: {}
      })}
    />
  );

  const message = getByTestId('message');
  expect(message.textContent).toBe('Method 2 message');
});

test('withMethodAvailability prefers override isAvailable over backend when override is false', () => {
  const WrappedComponent = withMethodAvailability(MockComponent);
  const { getByTestId } = render(
    <WrappedComponent
      {...makeProps({
        method: {
          urlSegment: 'webauthn',
          isAvailable: true,
          unavailableMessage: '',
        },
        availableMethodOverrides: {
          webauthn: {
            isAvailable: false,
            unavailableMessage: 'WebAuthn not supported in this browser',
          }
        }
      })}
    />
  );
  const available = getByTestId('available');
  expect(available.textContent).toBe('unavailable');
});

test('withMethodAvailability prefers override isAvailable over backend when override is true', () => {
  const WrappedComponent = withMethodAvailability(MockComponent);
  const { getByTestId } = render(
    <WrappedComponent
      {...makeProps({
        method: {
          urlSegment: 'webauthn',
          isAvailable: false,
          unavailableMessage: 'Method disabled',
        },
        availableMethodOverrides: {
          webauthn: {
            isAvailable: true,
          }
        }
      })}
    />
  );
  const available = getByTestId('available');
  expect(available.textContent).toBe('available');
});

test('withMethodAvailability uses backend availability when override has no isAvailable key', () => {
  const WrappedComponent = withMethodAvailability(MockComponent);
  const { getByTestId } = render(
    <WrappedComponent
      {...makeProps({
        method: {
          urlSegment: 'webauthn',
          isAvailable: true,
          unavailableMessage: 'Backend message',
        },
        availableMethodOverrides: {
          webauthn: {
            unavailableMessage: 'Override message only',
          }
        }
      })}
    />
  );
  const available = getByTestId('available');
  expect(available.textContent).toBe('available');
});

test('withMethodAvailability handles empty availableMethodOverrides object', () => {
  const WrappedComponent = withMethodAvailability(MockComponent);
  const { getByTestId } = render(
    <WrappedComponent
      {...makeProps({
        method: {
          urlSegment: 'test-method',
          isAvailable: true,
          unavailableMessage: '',
        },
        availableMethodOverrides: {}
      })}
    />
  );
  const available = getByTestId('available');
  expect(available.textContent).toBe('available');
});
