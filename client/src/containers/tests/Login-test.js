/* global jest, test, expect */

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
