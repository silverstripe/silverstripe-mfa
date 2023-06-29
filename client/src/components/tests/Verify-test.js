/* global jest, test, describe, it, expect */

import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { Component as Verify } from '../Verify';

let lastApiCallArgs;
let resolveApiCall;
// eslint-disable-next-line no-unused-vars
let rejectApiCall;

jest.mock('lib/api', () => ({
  __esModule: true,
  default: (endpoint, method, body, headers) => {
    lastApiCallArgs = { endpoint, method, body, headers };
    return new Promise((resolve, reject) => {
      resolveApiCall = resolve;
      rejectApiCall = reject;
    });
  }
}));

let lastInjectorLoadComponentArg;

jest.mock('lib/Injector', () => ({
  __esModule: true,
  loadComponent: (component) => {
    lastInjectorLoadComponentArg = component;
    return (props) => {
      const dataProps = Object.keys(props).map(k => `${k}=${props[k]}`).join(',');
      return <div
        data-component={component}
        data-props={dataProps}
        onClick={() => props.onCompleteVerification({
          my: 'verifydata'
        })}
      >
        {props.moreOptionsControl}
      </div>;
    };
  }
}));

window.ss = {
  i18n: {
    _t: (key, string) => string,
    inject: (string, map) => Object.entries(map).reduce(
      (acc, [key, value]) => acc.replace(key, value),
      string
    ),
  },
};

function makeProps(obj = {}) {
  return {
    endpoints: {
      verify: '/fake/{urlSegment}',
    },
    registeredMethods: [
      {
        urlSegment: 'aye',
        name: 'aye',
        component: 'TestMethod',
      },
      {
        urlSegment: 'bee',
        name: 'bee',
        component: 'TestMethod',
      },
    ],
    SelectMethodComponent: ({ methods }) => (
      <div data-testid="test-select-method">
        {methods.map(method => <div key={method.name} data-testid="test-select-method-method" data-method={method.name} />)}
      </div>
    ),
    onCompleteVerification: () => null,
    ...obj
  };
}

test('Verify chooses a default method if none is given', async () => {
  const { container } = render(
    <Verify {...makeProps()}/>
  );
  resolveApiCall({
    json: () => Promise.resolve({}),
  });
  await screen.findByText('Verify with {aye}');
  const titles = container.querySelectorAll('.mfa-section-title');
  expect(titles).toHaveLength(1);
  expect(titles[0].textContent).toBe('Verify with {aye}');
});

test('Verify chooses a given default method', async () => {
  const { container } = render(
    <Verify {...makeProps({
      defaultMethod: 'bee'
    })}
    />
  );
  resolveApiCall({
    json: () => Promise.resolve({}),
  });
  await screen.findByText('Verify with {bee}');
  const titles = container.querySelectorAll('.mfa-section-title');
  expect(titles).toHaveLength(1);
  expect(titles[0].textContent).toBe('Verify with {bee}');
});

test('Verify fetches schema from the given login endpoint', async () => {
  render(
    <Verify {...makeProps()}/>
  );
  resolveApiCall({
    json: () => Promise.resolve({}),
  });
  expect(lastApiCallArgs).toStrictEqual({
    body: undefined,
    endpoint: '/fake/aye',
    headers: undefined,
    method: undefined
  });
  // Do this final await to prevent "Warning: An update to Verify inside a test was not wrapped in act(...)"
  await screen.findByText('Verify with {aye}');
});

test('Verify loads the default method component', async () => {
  render(
    <Verify {...makeProps()}/>
  );
  resolveApiCall({
    json: () => Promise.resolve({}),
  });
  await screen.findByText('Verify with {aye}');
  expect(lastInjectorLoadComponentArg).toBe('TestMethod');
});

test('Verify forwards API response as props to injected component', async () => {
  const { container } = render(
    <Verify {...makeProps()}/>
  );
  resolveApiCall({
    json: () => Promise.resolve({
      myProp: 1,
      anotherProp: 'two',
    }),
  });
  await screen.findByText('Verify with {aye}');
  const method = container.querySelector('[data-component="TestMethod"]');
  expect(method.getAttribute('data-props')).toContain('myProp=1,anotherProp=two');
});

test('Verify handles a CSRF token provided with the props and adds it to state', async () => {
  const { container } = render(
    <Verify {...makeProps()}/>
  );
  resolveApiCall({
    json: () => Promise.resolve({
      SecurityID: 'test',
    }),
  });
  await screen.findByText('Verify with {aye}');
  const method = container.querySelector('[data-component="TestMethod"]');
  fireEvent.click(method);
  resolveApiCall({
    status: 200,
    json: () => Promise.resolve({ foo: 'bar' })
  });
  expect(lastApiCallArgs).toStrictEqual({
    body: '{"my":"verifydata"}',
    endpoint: '/fake/aye?SecurityID=test',
    headers: undefined,
    method: 'POST'
  });
});

test('Verify provides a control to choose other methods to the injected component', async () => {
  const { container } = render(
    <Verify {...makeProps()}/>
  );
  resolveApiCall({
    json: () => Promise.resolve({}),
  });
  await screen.findByText('Verify with {aye}');
  expect(container.querySelector('a.btn-secondary').textContent).toBe('More options');
});

test('Verify does not provides a control to choose other methods to the injected component when there are too few methods', async () => {
  const { container } = render(
    <Verify {...makeProps({
      registeredMethods: [makeProps().registeredMethods[0]]
    })}
    />
  );
  resolveApiCall({
    json: () => Promise.resolve({}),
  });
  await screen.findByText('Verify with {aye}');
  expect(container.querySelector('a.btn-secondary')).toBeNull();
});

test('Verify handles a click event on the show other methods control', async () => {
  render(
    <Verify {...makeProps()}/>
  );
  resolveApiCall({
    json: () => Promise.resolve({}),
  });
  await screen.findByText('Verify with {aye}');
  fireEvent.click(screen.getByText('More options'));
  resolveApiCall({
    status: 202
  });
  const method = await screen.findByTestId('test-select-method-method');
  expect(method.getAttribute('data-method')).toBe('bee');
});

test('Verify will use the login endpoint to verify a completed login', async () => {
  let doResolve;
  const promise = new Promise((resolve) => {
    doResolve = resolve;
  });
  const onCompleteVerification = jest.fn(() => doResolve());
  const { container } = render(
    <Verify {...makeProps({
      onCompleteVerification
    })}
    />
  );
  resolveApiCall({
    json: () => Promise.resolve({}),
  });
  await screen.findByText('Verify with {aye}');
  const method = container.querySelector('[data-component="TestMethod"]');
  fireEvent.click(method);
  resolveApiCall({
    status: 200,
    json: () => Promise.resolve({ foo: 'bar' })
  });
  await promise;
  expect(onCompleteVerification).toHaveBeenCalled();
});

test('Verify will provide a message from any unverified login to the injected component', async () => {
  const { container } = render(
    <Verify {...makeProps()}/>
  );
  resolveApiCall({
    json: () => Promise.resolve({}),
  });
  await screen.findByText('Verify with {aye}');
  const method = container.querySelector('[data-component="TestMethod"]');
  fireEvent.click(method);
  resolveApiCall({
    status: 400,
    json: () => Promise.resolve({
      message: 'It was a failure',
    })
  });
  await screen.findByText('Verify with {aye}');
  const methods = container.querySelectorAll('[data-component="TestMethod"]');
  expect(methods).toHaveLength(1);
  expect(methods[0].getAttribute('data-props')).toContain('error=It was a failure');
});

test('Verify will provide a try-again message for a 429 rate limiting code', async () => {
  const { container } = render(
    <Verify {...makeProps()}/>
  );
  resolveApiCall({
    json: () => Promise.resolve({}),
  });
  await screen.findByText('Verify with {aye}');
  const method = container.querySelector('[data-component="TestMethod"]');
  fireEvent.click(method);
  resolveApiCall({
    status: 429,
    json: () => Promise.resolve({
      message: 'Something went wrong. Please try again.',
    })
  });
  await screen.findByText('Verify with {aye}');
  const methods = container.querySelectorAll('[data-component="TestMethod"]');
  expect(methods).toHaveLength(1);
  expect(methods[0].getAttribute('data-props')).toContain('error=Something went wrong. Please try again.');
});

test('Verify will provide an unknown message for a 500 rate limiting code', async () => {
  const { container } = render(
    <Verify {...makeProps()}/>
  );
  resolveApiCall({
    json: () => Promise.resolve({}),
  });
  await screen.findByText('Verify with {aye}');
  const method = container.querySelector('[data-component="TestMethod"]');
  fireEvent.click(method);
  resolveApiCall({
    status: 500,
    json: () => Promise.resolve({
      message: 'An unknown error occurred.',
    })
  });
  await screen.findByText('Verify with {aye}');
  const methods = container.querySelectorAll('[data-component="TestMethod"]');
  expect(methods).toHaveLength(1);
  expect(methods[0].getAttribute('data-props')).toContain('error=An unknown error occurred.');
});

test('Verify renders an unavailable screen when the selected method is unavailable', async () => {
  render(
    <Verify {...makeProps({
      isAvailable: () => false,
      getUnavailableMessage: () => 'There is no spoon'
    })}
    />
  );
  resolveApiCall({
    json: () => Promise.resolve({}),
  });
  const el = await screen.findByText('There is no spoon');
  expect(el.parentNode.classList).toContain('mfa-method--unavailable');
});
