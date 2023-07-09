/* global jest, test, describe, it, expect */

// eslint-disable-next-line no-unused-vars
import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import AccountResetUI from '../AccountResetUI';

let resolveApiCall;
let rejectApiCall;
let lastApiCallArgs;

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

jest.mock('reactstrap-confirm', () => jest.fn().mockImplementation(
  () => Promise.resolve(true)
));

function makeProps(obj = {}) {
  return {
    // this is mocked to prevent a "<div> cannot appear as a descendant of <p>" warning
    LoadingIndicatorComponent: () => <em>loading</em>,
    ...obj
  };
}

test('AccountResetUI is disabled when an endpoint has not been supplied', () => {
  const { container } = render(<AccountResetUI {...makeProps()} />);
  expect(container.querySelector('.account-reset-action .btn').disabled).toBe(true);
});

test('AccountResetUI is enabled when an endpoint has been supplied', () => {
  const { container } = render(
    <AccountResetUI {...makeProps({
      resetEndpoint: '/reset/1'
    })}
    />
  );
  expect(container.querySelector('.account-reset-action .btn').disabled).toBe(false);
});

test('AccountResetUI submits the reset request when clicked and hides the button', async () => {
  const { container } = render(
    <AccountResetUI {...makeProps({
      resetEndpoint: '/reset/1'
    })}
    />
  );
  fireEvent.click(container.querySelector('.account-reset-action .btn'));
  await screen.findByText('Sending...');
  expect(container.querySelector('.account-reset-action .btn')).toBeNull();
  resolveApiCall({
    json: () => Promise.resolve({})
  });
  expect(lastApiCallArgs).toStrictEqual({
    body: '{"csrf_token":"SecurityID"}',
    endpoint: '/reset/1',
    headers: undefined,
    method: 'POST'
  });
  const el = await screen.findByText('An email has been sent.');
  expect(el.classList).toContain('account-reset-action__message');
});

test('AccountResetUI does not display a status by default', () => {
  const { container } = render(<AccountResetUI {...makeProps()} />);
  expect(container.querySelector('account-reset-action__message')).toBeNull();
});

test('AccountResetUI displays an error status when the request fails', async () => {
  const { container } = render(
    <AccountResetUI {...makeProps({
      resetEndpoint: '/reset/1'
    })}
    />
  );
  fireEvent.click(container.querySelector('.account-reset-action .btn'));
  await screen.findByText('Sending...');
  rejectApiCall();
  await screen.findByText('We were unable to send an email, please try again later.');
});
