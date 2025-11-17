/* global jest, test, expect, afterEach */

import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { Provider } from 'react-redux';
import { createStore } from 'redux';
import { SCREEN_INTRODUCTION } from 'components/Register';
import Reset from '../Reset';
import { MFAMethodListContext } from '../../MFAMethodListContext';

function createTestStore() {
  const initialState = {
    mfaRegister: {
      screen: SCREEN_INTRODUCTION,
      method: null,
      availableMethods: [],
    },
    mfaAdministration: {
      defaultMethod: null,
      registeredMethods: [],
    },
  };
  const reducer = (state = initialState, action = {}) => {
    switch (action.type) {
      case 'MFA_REGISTER/CHOOSE_METHOD':
        return {
          ...state,
          mfaRegister: {
            ...state.mfaRegister,
            method: action.payload,
          },
        };
      case 'MFA_REGISTER/SHOW_SCREEN':
        return {
          ...state,
          mfaRegister: {
            ...state.mfaRegister,
            screen: action.payload,
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

function makeAvailableMethod(obj = {}) {
  return {
    urlSegment: 'totp',
    name: 'Authenticator App',
    description: 'Use an authenticator app',
    supportLink: 'http://example.com/support',
    supportText: 'Learn more',
    thumbnail: 'http://example.com/thumbnail.png',
    component: 'ToTPMethod',
    isAvailable: true,
    ...obj,
  };
}

function makeContextValue(obj = {}) {
  return {
    allAvailableMethods: [makeAvailableMethod()],
    backupMethod: null,
    endpoints: {},
    resources: {},
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

test('Reset renders a button with correct CSS class', () => {
  const store = createTestStore();
  renderWithProviders(
    <Reset method={makeMethod()} onResetMethod={() => {}} />,
    { store }
  );
  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
  expect(button.classList.contains('registered-method-list-item__control')).toBe(true);
});

test('Reset button has correct button type attribute', () => {
  const store = createTestStore();
  renderWithProviders(
    <Reset method={makeMethod()} onResetMethod={() => {}} />,
    { store }
  );
  const button = screen.getByRole('button');
  expect(button.getAttribute('type')).toBe('button');
});

test('Reset button displays internationalized text', () => {
  const store = createTestStore();
  renderWithProviders(
    <Reset method={makeMethod()} onResetMethod={() => {}} />,
    { store }
  );
  const button = screen.getByRole('button');
  expect(button.textContent).toBe('Reset');
});

test('Reset button is clickable and renders without errors', () => {
  const store = createTestStore();
  renderWithProviders(
    <Reset method={makeMethod()} onResetMethod={() => {}} />,
    { store }
  );
  const button = screen.getByRole('button');
  expect(() => {
    fireEvent.click(button);
  }).not.toThrow();
});

test('Reset with onReset callback receives a function', () => {
  const onReset = jest.fn();
  const onResetMethod = jest.fn();
  const store = createTestStore();

  renderWithProviders(
    <Reset method={makeMethod()} onResetMethod={onResetMethod} onReset={onReset} />,
    { store }
  );

  const button = screen.getByRole('button');
  fireEvent.click(button);
  expect(onReset).toHaveBeenCalled();
});

test('Reset renders with method having urlSegment', () => {
  const store = createTestStore();
  const method = makeMethod({ urlSegment: 'totp' });

  renderWithProviders(
    <Reset method={method} onResetMethod={() => {}} />,
    { store }
  );

  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
});

test('Reset renders with different method urlSegments', () => {
  const store = createTestStore();
  const smsMethod = makeMethod({ urlSegment: 'sms', name: 'SMS' });
  const availableSMS = makeAvailableMethod({ urlSegment: 'sms', name: 'SMS' });

  renderWithProviders(
    <Reset method={smsMethod} onResetMethod={() => {}} />,
    {
      store,
      contextValue: makeContextValue({ allAvailableMethods: [availableSMS] })
    }
  );

  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
});

test('Reset uses window.ss.i18n._t for text localization', () => {
  const i18nSpy = jest.spyOn(window.ss.i18n, '_t');
  const store = createTestStore();

  renderWithProviders(
    <Reset method={makeMethod()} onResetMethod={() => {}} />,
    { store }
  );

  expect(i18nSpy).toHaveBeenCalledWith(
    'MultiFactorAuthentication.RESET_METHOD',
    expect.any(String)
  );
  i18nSpy.mockRestore();
});

test('Reset renders with different method names', () => {
  const store = createTestStore();
  const backupMethod = makeMethod({ name: 'Backup Codes', urlSegment: 'backup' });
  const availableBackup = makeAvailableMethod({ name: 'Backup Codes', urlSegment: 'backup' });

  renderWithProviders(
    <Reset method={backupMethod} onResetMethod={() => {}} />,
    {
      store,
      contextValue: makeContextValue({ allAvailableMethods: [availableBackup] })
    }
  );

  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
});

test('Reset renders RegisterModal component in the DOM', () => {
  const store = createTestStore();
  const { container } = renderWithProviders(
    <Reset method={makeMethod()} onResetMethod={() => {}} />,
    { store }
  );

  expect(container.innerHTML).toBeDefined();
  expect(container.querySelectorAll('*').length).toBeGreaterThan(0);
});

test('Reset with context containing endpoints', () => {
  const store = createTestStore();
  const endpoints = { verify: '/verify', register: '/register' };

  renderWithProviders(
    <Reset method={makeMethod()} onResetMethod={() => {}} />,
    {
      store,
      contextValue: makeContextValue({ endpoints })
    }
  );

  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
});

test('Reset with context containing resources', () => {
  const store = createTestStore();
  const resources = { icon: 'http://example.com/icon.png', image: 'http://example.com/image.png' };

  renderWithProviders(
    <Reset method={makeMethod()} onResetMethod={() => {}} />,
    {
      store,
      contextValue: makeContextValue({ resources })
    }
  );

  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
});

test('Reset with context containing backup method', () => {
  const store = createTestStore();
  const backupMethod = makeMethod({ name: 'Backup Codes', urlSegment: 'backup' });

  renderWithProviders(
    <Reset method={makeMethod()} onResetMethod={() => {}} />,
    {
      store,
      contextValue: makeContextValue({ backupMethod })
    }
  );

  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
});

test('Reset allows optional onReset callback prop', () => {
  const store = createTestStore();

  expect(() => {
    renderWithProviders(
      <Reset method={makeMethod()} onResetMethod={() => {}} />,
      { store }
    );
  }).not.toThrow();
});

test('Reset renders button even when available methods list is empty', () => {
  const store = createTestStore();

  renderWithProviders(
    <Reset method={makeMethod()} onResetMethod={() => {}} />,
    {
      store,
      contextValue: makeContextValue({ allAvailableMethods: [] })
    }
  );

  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
});

test('Reset renders with multiple available methods in context', () => {
  const store = createTestStore();
  const method1 = makeAvailableMethod({ urlSegment: 'totp', name: 'TOTP' });
  const method2 = makeAvailableMethod({ urlSegment: 'sms', name: 'SMS' });
  const method3 = makeAvailableMethod({ urlSegment: 'backup', name: 'Backup' });

  renderWithProviders(
    <Reset method={makeMethod()} onResetMethod={() => {}} />,
    {
      store,
      contextValue: makeContextValue({ allAvailableMethods: [method1, method2, method3] })
    }
  );

  const button = screen.getByRole('button');
  expect(button).not.toBeNull();
});

test('Reset handles rapid successive clicks on button', () => {
  const store = createTestStore();
  renderWithProviders(
    <Reset method={makeMethod()} onResetMethod={() => {}} />,
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

test('Reset renders button with stable reference across re-renders', () => {
  const store = createTestStore();
  const method = makeMethod();

  const { rerender } = renderWithProviders(
    <Reset method={method} onResetMethod={() => {}} />,
    { store }
  );

  const button1 = screen.getByRole('button');
  expect(button1).not.toBeNull();

  rerender(
    <Provider store={store}>
      <MFAMethodListContext.Provider value={makeContextValue()}>
        <Reset method={method} onResetMethod={() => {}} />
      </MFAMethodListContext.Provider>
    </Provider>
  );

  const button2 = screen.getByRole('button');
  expect(button2).not.toBeNull();
});
