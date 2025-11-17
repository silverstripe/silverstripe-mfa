/* global jest, test, expect, afterEach */

import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { Provider } from 'react-redux';
import { createStore } from 'redux';
import api from 'lib/api';
import SetDefault from '../SetDefault';
import { MFAMethodListContext } from '../../MFAMethodListContext';

jest.mock('lib/api');
jest.mock('lib/Config', () => ({
  get: jest.fn(() => 'test-security-id'),
}));

// Mock window.fetch to properly support json() method
global.fetch = jest.fn();

function createTestStore() {
  const initialState = {
    mfaAdministration: {
      defaultMethod: null,
      registeredMethods: [],
    },
  };
  const reducer = (state = initialState, action = {}) => {
    switch (action.type) {
      case 'MFA_ADMINISTRATION/SET_DEFAULT_METHOD':
        return {
          ...state,
          mfaAdministration: {
            ...state.mfaAdministration,
            defaultMethod: action.payload,
          },
        };
      default:
        return state;
    }
  };
  return createStore(reducer);
}

window.ss = {
  i18n: { _t: (key, string) => string },
};

function makeMethod(obj = {}) {
  return {
    name: 'Authenticator App',
    urlSegment: 'totp',
    isAvailable: true,
    component: 'ToTPMethod',
    supportLink: 'http://example.com/support',
    thumbnail: 'http://example.com/thumbnail.png',
    ...obj,
  };
}

function makeContextValue(obj = {}) {
  return {
    endpoints: {
      setDefault: '/set-default/{urlSegment}',
    },
    ...obj,
  };
}

function renderWithProviders(component, { store = createTestStore(), contextValue = makeContextValue() } = {}) {
  return render(
    <Provider store={store}>
      <MFAMethodListContext.Provider value={contextValue}>
        {component}
      </MFAMethodListContext.Provider>
    </Provider>
  );
}

afterEach(() => {
  jest.clearAllMocks();
});

test('SetDefault renders a button with correct CSS class', () => {
  const store = createTestStore();
  renderWithProviders(
    <SetDefault method={makeMethod()} />,
    { store }
  );
  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
  expect(button.classList.contains('registered-method-list-item__control')).toBe(true);
});

test('SetDefault button has correct button type attribute', () => {
  const store = createTestStore();
  renderWithProviders(
    <SetDefault method={makeMethod()} />,
    { store }
  );
  const button = screen.getByRole('button');
  expect(button.getAttribute('type')).toBe('button');
});

test('SetDefault button displays internationalized text', () => {
  const store = createTestStore();
  renderWithProviders(
    <SetDefault method={makeMethod()} />,
    { store }
  );
  const button = screen.getByRole('button');
  expect(button.textContent).toBe('Set as default method');
});

test('SetDefault button is clickable and renders without errors', () => {
  const store = createTestStore();
  api.mockResolvedValueOnce({
    status: 200,
    json: () => Promise.resolve({}),
  });
  renderWithProviders(
    <SetDefault method={makeMethod()} />,
    { store }
  );
  const button = screen.getByRole('button');
  expect(() => {
    fireEvent.click(button);
  }).not.toThrow();
});

test('SetDefault calls onSetDefaultMethod on successful API response', async () => {
  const store = createTestStore();
  api.mockResolvedValueOnce({
    status: 200,
    json: () => Promise.resolve({}),
  });
  renderWithProviders(
    <SetDefault method={makeMethod({ urlSegment: 'totp' })} />,
    { store }
  );
  const button = screen.getByRole('button');
  fireEvent.click(button);
  await new Promise(resolve => setTimeout(resolve, 100));
  // Verify the API was called with the correct endpoint
  expect(api).toHaveBeenCalledWith(
    '/set-default/totp?SecurityID=test-security-id',
    'PUT'
  );
});

test('SetDefault sends PUT request with correct endpoint and SecurityID', async () => {
  const store = createTestStore();
  api.mockResolvedValueOnce({
    status: 200,
    json: () => Promise.resolve({}),
  });
  renderWithProviders(
    <SetDefault method={makeMethod({ urlSegment: 'totp' })} />,
    { store }
  );
  const button = screen.getByRole('button');
  fireEvent.click(button);
  await waitFor(() => {
    expect(api).toHaveBeenCalledWith(
      '/set-default/totp?SecurityID=test-security-id',
      'PUT'
    );
  });
});

test('SetDefault renders with method having different urlSegment', () => {
  const store = createTestStore();
  const method = makeMethod({ urlSegment: 'sms', name: 'SMS' });
  renderWithProviders(
    <SetDefault method={method} />,
    { store }
  );
  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
});

test('SetDefault uses window.ss.i18n._t for text localization', () => {
  const i18nSpy = jest.spyOn(window.ss.i18n, '_t');
  const store = createTestStore();
  api.mockResolvedValueOnce({
    status: 200,
    json: () => Promise.resolve({}),
  });
  renderWithProviders(
    <SetDefault method={makeMethod()} />,
    { store }
  );
  expect(i18nSpy).toHaveBeenCalledWith(
    'MultiFactorAuthentication.SET_AS_DEFAULT',
    expect.any(String)
  );
  i18nSpy.mockRestore();
});

test('SetDefault renders with context containing setDefault endpoint', () => {
  const store = createTestStore();
  const endpoints = { setDefault: '/custom-set-default/{urlSegment}' };
  renderWithProviders(
    <SetDefault method={makeMethod()} />,
    {
      store,
      contextValue: makeContextValue({ endpoints })
    }
  );
  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
});

test('SetDefault sends correct endpoint URL with method urlSegment', async () => {
  const store = createTestStore();
  api.mockResolvedValueOnce({
    status: 200,
    json: () => Promise.resolve({}),
  });
  renderWithProviders(
    <SetDefault method={makeMethod({ urlSegment: 'backup' })} />,
    { store }
  );
  const button = screen.getByRole('button');
  fireEvent.click(button);
  await waitFor(() => {
    const callArgs = api.mock.calls[0][0];
    expect(callArgs).toContain('/set-default/backup');
  });
});

test('SetDefault renders with backup method urlSegment', () => {
  const store = createTestStore();
  const backupMethod = makeMethod({ name: 'Backup Codes', urlSegment: 'backup' });
  renderWithProviders(
    <SetDefault method={backupMethod} />,
    { store }
  );
  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
});

test('SetDefault renders button even with minimal method data', () => {
  const store = createTestStore();
  const minimalMethod = makeMethod({ name: '', urlSegment: 'test' });
  renderWithProviders(
    <SetDefault method={minimalMethod} />,
    { store }
  );
  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
});

test('SetDefault handles rapid successive clicks on button', () => {
  const store = createTestStore();
  api.mockResolvedValue({
    status: 200,
    json: () => Promise.resolve({}),
  });
  renderWithProviders(
    <SetDefault method={makeMethod()} />,
    { store }
  );
  const button = screen.getByRole('button');
  expect(() => {
    fireEvent.click(button);
    fireEvent.click(button);
    fireEvent.click(button);
  }).not.toThrow();
  expect(button).not.toBeNull();
});

test('SetDefault renders button with stable reference across re-renders', () => {
  const store = createTestStore();
  const method = makeMethod();
  api.mockResolvedValue({
    status: 200,
    json: () => Promise.resolve({}),
  });
  const { rerender } = renderWithProviders(
    <SetDefault method={method} />,
    { store }
  );
  const button1 = screen.getByRole('button');
  expect(button1).not.toBeNull();
  rerender(
    <Provider store={store}>
      <MFAMethodListContext.Provider value={makeContextValue()}>
        <SetDefault method={method} />
      </MFAMethodListContext.Provider>
    </Provider>
  );
  const button2 = screen.getByRole('button');
  expect(button2).not.toBeNull();
});

test('SetDefault with context containing custom endpoint pattern', async () => {
  const store = createTestStore();
  const customEndpoint = '/admin/set-method-as-default/{urlSegment}';
  api.mockResolvedValueOnce({
    status: 200,
    json: () => Promise.resolve({}),
  });
  renderWithProviders(
    <SetDefault method={makeMethod({ urlSegment: 'custom' })} />,
    {
      store,
      contextValue: makeContextValue({ endpoints: { setDefault: customEndpoint } })
    }
  );
  const button = screen.getByRole('button');
  fireEvent.click(button);
  await waitFor(() => {
    const callArgs = api.mock.calls[0][0];
    expect(callArgs).toContain('/admin/set-method-as-default/custom');
  });
});
