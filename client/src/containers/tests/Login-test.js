/* global jest, test, expect, beforeEach, afterEach */

import React from 'react';
import { render, screen, waitForElementToBeRemoved } from '@testing-library/react';
import { Component as Login } from '../Login';

// eslint-disable-next-line no-unused-vars
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

const mapPropsToDataAttributes = (props) => {
  const dataAttributes = {};
  Object.keys(props).forEach((key) => {
    if (key !== 'children' && key !== 'ref') {
      const dataKey = `data-${key.toLowerCase()}`;
      dataAttributes[dataKey] = String(props[key]);
    }
  });
  return dataAttributes;
};

jest.mock('components/Verify', () => ({
  __esModule: true,
  default: (props) => {
    const dataProps = mapPropsToDataAttributes(props);
    return (
      <div
        className="mfa-verify"
        data-testid="verify-component"
        {...dataProps}
      />
    );
  },
}));

jest.mock('components/Register', () => ({
  __esModule: true,
  default: (props) => {
    const dataProps = mapPropsToDataAttributes(props);
    return (
      <div
        className="mfa-register"
        data-testid="register-component"
        {...dataProps}
      />
    );
  },
}));

window.ss = {
  i18n: { _t: (key, string) => string },
};

const firstMethod = {
  urlSegment: 'aye',
  name: 'Aye',
  description: 'Register using aye',
  supportLink: 'https://google.com',
  component: 'TestRegistration',
};
const secondMethod = {
  urlSegment: 'bee',
  name: 'Bee',
  description: 'Register using bee',
  supportLink: '',
  component: 'TestRegistration',
};

test('Login componentDidMount() handles schema fetch errors', async () => {
  // Note you can't trigger an error by returning a 200 with an empty schema here as you'll
  // get multiple other javascript errors instead of the "graceful" error state is triggered
  render(
    <Login {...{
      schemaURL: '/foo',
    }}
    />
  );
  resolveApiCall({
    status: 500
  });
  expect(await screen.findByText('Something went wrong!')).not.toBeNull();
});

test('Login componentDidMount() handles successful schema fetch', async () => {
  let doResolve;
  const promise = new Promise((resolve) => {
    doResolve = resolve;
  });
  const onSetAvailableMethods = jest.fn(() => doResolve());
  const { container } = render(
    <Login {...{
      schemaURL: '/foo',
      onSetAvailableMethods
    }}
    />
  );
  const indicator = container.querySelector('.mfa-loading-indicator');
  resolveApiCall({
    status: 200,
    json: () => Promise.resolve({
      availableMethods: [firstMethod, secondMethod],
      allMethods: [],
      backupMethod: [],
      registeredMethods: [],
      isFullyRegistered: true,
    }),
  });
  await waitForElementToBeRemoved(indicator);
  await promise;
  expect(onSetAvailableMethods).toBeCalled();
});

test('Login calls onSetAllMethods on successful schema fetch', async () => {
  let doResolve;
  const promise = new Promise((resolve) => {
    doResolve = resolve;
  });
  const onSetAllMethods = jest.fn(() => doResolve());
  const onSetAvailableMethods = jest.fn();
  const { container } = render(
    <Login {...{
      schemaURL: '/foo',
      onSetAllMethods,
      onSetAvailableMethods
    }}
    />
  );
  const indicator = container.querySelector('.mfa-loading-indicator');
  const allMethods = [firstMethod, secondMethod];
  resolveApiCall({
    status: 200,
    json: () => Promise.resolve({
      availableMethods: [firstMethod],
      allMethods,
      backupMethod: firstMethod,
      registeredMethods: [firstMethod],
      isFullyRegistered: false,
    }),
  });
  await waitForElementToBeRemoved(indicator);
  await promise;
  expect(onSetAllMethods).toHaveBeenCalledWith(allMethods);
});

test('Login renders Verify component when schema has registered methods', async () => {
  let doResolve;
  const promise = new Promise((resolve) => {
    doResolve = resolve;
  });
  const onSetAvailableMethods = jest.fn(() => doResolve());
  const { container } = render(
    <Login {...{
      schemaURL: '/foo',
      onSetAvailableMethods
    }}
    />
  );
  const indicator = container.querySelector('.mfa-loading-indicator');
  resolveApiCall({
    status: 200,
    json: () => Promise.resolve({
      availableMethods: [firstMethod],
      allMethods: [firstMethod, secondMethod],
      backupMethod: firstMethod,
      registeredMethods: [firstMethod],
      isFullyRegistered: false,
      endpoints: {
        complete: '/complete'
      }
    }),
  });
  await waitForElementToBeRemoved(indicator);
  await promise;
  expect(screen.getByTestId('verify-component')).not.toBeNull();
});

test('Login renders Register component when no registered methods', async () => {
  let doResolve;
  const promise = new Promise((resolve) => {
    doResolve = resolve;
  });
  const onSetAvailableMethods = jest.fn(() => doResolve());
  const { container } = render(
    <Login {...{
      schemaURL: '/foo',
      onSetAvailableMethods
    }}
    />
  );
  const indicator = container.querySelector('.mfa-loading-indicator');
  resolveApiCall({
    status: 200,
    json: () => Promise.resolve({
      availableMethods: [firstMethod],
      allMethods: [firstMethod, secondMethod],
      backupMethod: firstMethod,
      registeredMethods: [],
      isFullyRegistered: false,
      endpoints: {
        register: '/register',
        complete: '/complete'
      }
    }),
  });
  await waitForElementToBeRemoved(indicator);
  await promise;
  expect(screen.getByTestId('register-component')).not.toBeNull();
});

test('Login renders loading indicator while schema is being loaded', () => {
  const { container } = render(
    <Login {...{
      schemaURL: '/foo',
    }}
    />
  );
  const indicator = container.querySelector('.mfa-loading-indicator');
  expect(indicator).not.toBeNull();
  expect(indicator.classList.contains('mfa-loading-indicator--block')).toBe(true);
});

test('Login renders error screen when schema load fails with non-200 status', async () => {
  render(
    <Login {...{
      schemaURL: '/foo',
    }}
    />
  );
  resolveApiCall({
    status: 404
  });
  expect(await screen.findByText('Something went wrong!')).not.toBeNull();
  const button = screen.getByText('Try again');
  expect(button).not.toBeNull();
  expect(button.classList.contains('btn')).toBe(true);
  expect(button.classList.contains('btn-outline-secondary')).toBe(true);
});
