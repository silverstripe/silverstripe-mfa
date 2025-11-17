/* global jest, test, describe, it, expect, afterEach */

// eslint-disable-next-line no-unused-vars
import React from 'react';
import { render, screen, fireEvent, waitFor, act } from '@testing-library/react';
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
  await act(async () => {
    resolveApiCall({
      json: () => Promise.resolve({})
    });
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
  await act(async () => {
    rejectApiCall();
  });
  await screen.findByText('We were unable to send an email, please try again later.');
});

test('AccountResetUI displays error status when API response contains error', async () => {
  const { container } = render(
    <AccountResetUI {...makeProps({
      resetEndpoint: '/reset/1'
    })}
    />
  );
  fireEvent.click(container.querySelector('.account-reset-action .btn'));
  await screen.findByText('Sending...');
  await act(async () => {
    resolveApiCall({
      json: () => Promise.resolve({ error: 'Something went wrong' })
    });
  });
  await screen.findByText('We were unable to send an email, please try again later.');
});

test('AccountResetUI renders correct title and description', () => {
  const { container } = render(<AccountResetUI {...makeProps()} />);
  const title = container.querySelector('.account-reset__title');
  const description = container.querySelector('.account-reset__description');
  expect(title).not.toBeNull();
  expect(description).not.toBeNull();
});

test('AccountResetUI hides button during submission', async () => {
  const { container } = render(
    <AccountResetUI {...makeProps({
      resetEndpoint: '/reset/1'
    })}
    />
  );
  const button = container.querySelector('.account-reset-action .btn');
  expect(button).not.toBeNull();
  fireEvent.click(button);
  await screen.findByText('Sending...');
  expect(container.querySelector('.account-reset-action .btn')).toBeNull();
});

test('AccountResetUI shows success message after successful request', async () => {
  render(
    <AccountResetUI {...makeProps({
      resetEndpoint: '/reset/1'
    })}
    />
  );
  const button = screen.getByRole('button', { name: 'Send account reset email' });
  fireEvent.click(button);
  await screen.findByText('Sending...');
  await act(async () => {
    resolveApiCall({
      json: () => Promise.resolve({})
    });
  });
  await screen.findByText('An email has been sent.');
});

test('AccountResetUI sends CSRF token in request body', async () => {
  render(
    <AccountResetUI {...makeProps({
      resetEndpoint: '/reset/1'
    })}
    />
  );
  const button = screen.getByRole('button', { name: 'Send account reset email' });
  fireEvent.click(button);
  await screen.findByText('Sending...');
  await act(async () => {
    resolveApiCall({
      json: () => Promise.resolve({})
    });
  });
  expect(JSON.parse(lastApiCallArgs.body).csrf_token).toBe('SecurityID');
});

test('AccountResetUI uses custom LoadingIndicatorComponent when provided', async () => {
  const CustomLoading = () => <span data-testid="custom-loader">Custom Loading</span>;
  render(
    <AccountResetUI {...makeProps({
      resetEndpoint: '/reset/1',
      LoadingIndicatorComponent: CustomLoading
    })}
    />
  );
  const button = screen.getByRole('button', { name: 'Send account reset email' });
  fireEvent.click(button);
  await waitFor(() => {
    expect(screen.getByTestId('custom-loader')).not.toBeNull();
  });
});

test('AccountResetUI displays CircleDash icon on failure', async () => {
  const { container } = render(
    <AccountResetUI {...makeProps({
      resetEndpoint: '/reset/1'
    })}
    />
  );
  const button = container.querySelector('.account-reset-action .btn');
  fireEvent.click(button);
  await screen.findByText('Sending...');
  await act(async () => {
    rejectApiCall();
  });
  await screen.findByText('We were unable to send an email, please try again later.');
  const failureIcon = container.querySelector('.account-reset-action--failure .account-reset-action__icon');
  expect(failureIcon).not.toBeNull();
});

test('AccountResetUI displays CircleTick icon on success', async () => {
  const { container } = render(
    <AccountResetUI {...makeProps({
      resetEndpoint: '/reset/1'
    })}
    />
  );
  const button = container.querySelector('.account-reset-action .btn');
  fireEvent.click(button);
  await screen.findByText('Sending...');
  await act(async () => {
    resolveApiCall({
      json: () => Promise.resolve({})
    });
  });
  await screen.findByText('An email has been sent.');
  const successIcon = container.querySelector('.account-reset-action--success .account-reset-action__icon');
  expect(successIcon).not.toBeNull();
});

test('AccountResetUI does not hide button when endpoint is not supplied', () => {
  const { container } = render(<AccountResetUI {...makeProps()} />);
  const button = container.querySelector('.account-reset-action .btn');
  expect(button).not.toBeNull();
  expect(button.disabled).toBe(true);
});

test('AccountResetUI contains root container with correct class', () => {
  const { container } = render(<AccountResetUI {...makeProps()} />);
  const rootContainer = container.querySelector('.account-reset');
  expect(rootContainer).not.toBeNull();
});

test('AccountResetUI renders sending status with correct CSS classes', async () => {
  const { container } = render(
    <AccountResetUI {...makeProps({
      resetEndpoint: '/reset/1'
    })}
    />
  );
  const button = container.querySelector('.account-reset-action .btn');
  fireEvent.click(button);
  await screen.findByText('Sending...');
  const sendingContainer = container.querySelector('.account-reset-action--sending');
  expect(sendingContainer).not.toBeNull();
  expect(sendingContainer.classList.contains('account-reset-action')).toBe(true);
});

test('AccountResetUI renders success status with correct CSS classes', async () => {
  const { container } = render(
    <AccountResetUI {...makeProps({
      resetEndpoint: '/reset/1'
    })}
    />
  );
  const button = container.querySelector('.account-reset-action .btn');
  fireEvent.click(button);
  await screen.findByText('Sending...');
  await act(async () => {
    resolveApiCall({
      json: () => Promise.resolve({})
    });
  });
  await screen.findByText('An email has been sent.');
  const successContainer = container.querySelector('.account-reset-action--success');
  expect(successContainer).not.toBeNull();
  expect(successContainer.classList.contains('account-reset-action')).toBe(true);
});

test('AccountResetUI renders failure status with correct CSS classes', async () => {
  const { container } = render(
    <AccountResetUI {...makeProps({
      resetEndpoint: '/reset/1'
    })}
    />
  );
  const button = container.querySelector('.account-reset-action .btn');
  fireEvent.click(button);
  await screen.findByText('Sending...');
  await act(async () => {
    rejectApiCall();
  });
  await screen.findByText('We were unable to send an email, please try again later.');
  const failureContainer = container.querySelector('.account-reset-action--failure');
  expect(failureContainer).not.toBeNull();
  expect(failureContainer.classList.contains('account-reset-action')).toBe(true);
});
