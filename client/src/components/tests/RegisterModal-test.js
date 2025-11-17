/* global jest, test, expect */

import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { Component as RegisterModal } from '../RegisterModal';
import { SCREEN_INTRODUCTION } from '../Register';

jest.mock('reactstrap', () => ({
  Modal: ({ isOpen, toggle, className, children }) => isOpen && (
    <div
      data-testid="modal"
      className={className}
      onClick={(e) => {
        if (e.target === e.currentTarget) toggle();
      }}
    >
      {children}
    </div>
  ),
  ModalHeader: ({ toggle, children }) => (
    <div data-testid="modal-header">
      <button data-testid="modal-close-btn" onClick={toggle}>Close</button>
      {children}
    </div>
  ),
  ModalBody: ({ className, children }) => (
    <div data-testid="modal-body" className={className}>
      {children}
    </div>
  ),
}));

jest.mock('components/Register/Title', () => ({
  __esModule: true,
  default: () => <div data-testid="title-component">Test Title</div>,
}));

window.ss = {
  i18n: { _t: (key, fallback) => fallback },
};

const mockRegisteredMethod = {
  name: 'Test Method',
  urlSegment: 'test-method',
  isAvailable: true,
  component: 'TestRegistration',
};

const mockRegisteredMethod2 = {
  name: 'Another Method',
  urlSegment: 'another-method',
  isAvailable: true,
  component: 'AnotherRegistration',
};

function makeProps(obj = {}) {
  return {
    isOpen: false,
    toggle: jest.fn(),
    disallowedScreens: [],
    backupMethod: null,
    endpoints: {
      register: '/register',
    },
    registeredMethods: [],
    registrationScreen: SCREEN_INTRODUCTION,
    resources: {},
    RegisterComponent: ({ onRegister, onCompleteRegistration }) => (
      <div data-testid="register-component">
        <button
          data-testid="register-btn"
          onClick={() => onRegister(mockRegisteredMethod)}
        >
          Register
        </button>
        <button
          data-testid="complete-btn"
          onClick={onCompleteRegistration}
        >
          Complete
        </button>
      </div>
    ),
    onAddRegisteredMethod: jest.fn(),
    onSetDefaultMethod: jest.fn(),
    ...obj,
  };
}

test('RegisterModal renders nothing when isOpen is false', () => {
  render(<RegisterModal {...makeProps()} />);
  expect(screen.queryByTestId('modal')).toBeNull();
});

test('RegisterModal renders Modal when isOpen is true', () => {
  render(<RegisterModal {...makeProps({ isOpen: true })} />);
  expect(screen.getByTestId('modal')).not.toBeNull();
});

test('RegisterModal renders Title in ModalHeader when isOpen is true', () => {
  render(<RegisterModal {...makeProps({ isOpen: true })} />);
  expect(screen.getByTestId('title-component')).not.toBeNull();
});

test('RegisterModal renders RegisterComponent when isOpen is true and registrationScreen is not SCREEN_INTRODUCTION', () => {
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
      })}
    />
  );
  expect(screen.getByTestId('register-component')).not.toBeNull();
});

test('RegisterModal does not render RegisterComponent when registrationScreen is SCREEN_INTRODUCTION', () => {
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: SCREEN_INTRODUCTION,
      })}
    />
  );
  expect(screen.queryByTestId('register-component')).toBeNull();
});

test('RegisterModal calls toggle when ModalHeader close button is clicked', () => {
  const toggle = jest.fn();
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        toggle,
      })}
    />
  );
  const closeBtn = screen.getByTestId('modal-close-btn');
  fireEvent.click(closeBtn);
  expect(toggle).toHaveBeenCalled();
});

test('RegisterModal applies correct className to Modal', () => {
  const { container } = render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
      })}
    />
  );
  const modal = container.querySelector('[data-testid="modal"]');
  expect(modal.classList.contains('registered-mfa-method-list-field-register-modal')).toBe(true);
});

test('RegisterModal applies correct className to ModalBody', () => {
  const { container } = render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
      })}
    />
  );
  const modalBody = container.querySelector('[data-testid="modal-body"]');
  expect(modalBody.classList.contains('registered-mfa-method-list-field-register-modal__content')).toBe(true);
});

test('RegisterModal calls onSetDefaultMethod when first method is registered', () => {
  const onAddRegisteredMethod = jest.fn();
  const onSetDefaultMethod = jest.fn();
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        registeredMethods: [],
        onAddRegisteredMethod,
        onSetDefaultMethod,
      })}
    />
  );
  const registerBtn = screen.getByTestId('register-btn');
  fireEvent.click(registerBtn);
  expect(onSetDefaultMethod).toHaveBeenCalledWith('test-method');
});

test('RegisterModal does not call onSetDefaultMethod when method is already registered', () => {
  const onAddRegisteredMethod = jest.fn();
  const onSetDefaultMethod = jest.fn();
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        registeredMethods: [mockRegisteredMethod],
        onAddRegisteredMethod,
        onSetDefaultMethod,
      })}
    />
  );
  const registerBtn = screen.getByTestId('register-btn');
  fireEvent.click(registerBtn);
  expect(onSetDefaultMethod).not.toHaveBeenCalled();
});

test('RegisterModal calls onAddRegisteredMethod when RegisterComponent calls onRegister', () => {
  const onAddRegisteredMethod = jest.fn();
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        onAddRegisteredMethod,
      })}
    />
  );
  const registerBtn = screen.getByTestId('register-btn');
  fireEvent.click(registerBtn);
  expect(onAddRegisteredMethod).toHaveBeenCalledWith(mockRegisteredMethod);
});

test('RegisterModal passes correct props to RegisterComponent', () => {
  const endpoints = { register: '/custom/register' };
  const resources = { test: 'resource' };
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        endpoints,
        resources,
        RegisterComponent: ({ endpoints: passedEndpoints, resources: passedResources }) => (
          <div data-testid="register-component">
            <div data-testid="endpoints-test">{passedEndpoints?.register}</div>
            <div data-testid="resources-test">{passedResources?.test}</div>
          </div>
        ),
      })}
    />
  );
  expect(screen.getByTestId('register-component')).not.toBeNull();
});

test('RegisterModal toggles when disallowedScreen is shown while modal is open', () => {
  const toggle = jest.fn();
  const { rerender } = render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        disallowedScreens: [SCREEN_INTRODUCTION],
        toggle,
      })}
    />
  );
  toggle.mockClear();
  rerender(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: SCREEN_INTRODUCTION,
        disallowedScreens: [SCREEN_INTRODUCTION],
        toggle,
      })}
    />
  );
  expect(toggle).toHaveBeenCalled();
});

test('RegisterModal does not toggle when modal is closed and disallowedScreen is shown', () => {
  const toggle = jest.fn();
  render(
    <RegisterModal
      {...makeProps({
        isOpen: false,
        registrationScreen: SCREEN_INTRODUCTION,
        disallowedScreens: [SCREEN_INTRODUCTION],
        toggle,
      })}
    />
  );
  expect(toggle).not.toHaveBeenCalled();
});

test('RegisterModal does not toggle when disallowedScreens array is empty', () => {
  const toggle = jest.fn();
  const { rerender } = render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        disallowedScreens: [],
        toggle,
      })}
    />
  );
  toggle.mockClear();
  rerender(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: SCREEN_INTRODUCTION,
        disallowedScreens: [],
        toggle,
      })}
    />
  );
  expect(toggle).not.toHaveBeenCalled();
});

test('RegisterModal does not toggle when registrationScreen is not in disallowedScreens', () => {
  const toggle = jest.fn();
  const { rerender } = render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        disallowedScreens: [SCREEN_INTRODUCTION],
        toggle,
      })}
    />
  );
  toggle.mockClear();
  rerender(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 3,
        disallowedScreens: [SCREEN_INTRODUCTION],
        toggle,
      })}
    />
  );
  expect(toggle).not.toHaveBeenCalled();
});

test('RegisterModal passes backupMethod to RegisterComponent', () => {
  const backupMethod = mockRegisteredMethod;
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        backupMethod,
      })}
    />
  );
  expect(screen.getByTestId('register-component')).not.toBeNull();
});

test('RegisterModal passes registeredMethods to RegisterComponent', () => {
  const registeredMethods = [mockRegisteredMethod, mockRegisteredMethod2];
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        registeredMethods,
      })}
    />
  );
  expect(screen.getByTestId('register-component')).not.toBeNull();
});

test('RegisterModal passes showTitle false to RegisterComponent', () => {
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        RegisterComponent: ({ showTitle }) => (
          <div data-testid="register-component" data-show-title={showTitle}>
            Test
          </div>
        ),
      })}
    />
  );
  const component = screen.getByTestId('register-component');
  expect(component.getAttribute('data-show-title')).toBe('false');
});

test('RegisterModal passes showSubTitle false to RegisterComponent', () => {
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        RegisterComponent: ({ showSubTitle }) => (
          <div data-testid="register-component" data-show-subtitle={showSubTitle}>
            Test
          </div>
        ),
      })}
    />
  );
  const component = screen.getByTestId('register-component');
  expect(component.getAttribute('data-show-subtitle')).toBe('false');
});

test('RegisterModal passes completeMessage to RegisterComponent', () => {
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        RegisterComponent: ({ completeMessage: msg }) => (
          <div data-testid="register-component" data-complete-msg={msg}>
            Test
          </div>
        ),
      })}
    />
  );
  const component = screen.getByTestId('register-component');
  expect(component.getAttribute('data-complete-msg')).toBeDefined();
});

test('RegisterModal passes onCompleteRegistration to RegisterComponent', () => {
  const toggle = jest.fn();
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        toggle,
      })}
    />
  );
  const completeBtn = screen.getByTestId('complete-btn');
  fireEvent.click(completeBtn);
  expect(toggle).toHaveBeenCalled();
});

test('RegisterModal renders with default props', () => {
  const { container } = render(<RegisterModal {...makeProps()} />);
  expect(container).not.toBeNull();
});

test('RegisterModal handles multiple disallowed screens', () => {
  const toggle = jest.fn();
  const SCREEN_2 = 2;
  const SCREEN_3 = 3;
  const { rerender } = render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 4,
        disallowedScreens: [SCREEN_2, SCREEN_3],
        toggle,
      })}
    />
  );
  toggle.mockClear();
  rerender(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: SCREEN_2,
        disallowedScreens: [SCREEN_2, SCREEN_3],
        toggle,
      })}
    />
  );
  expect(toggle).toHaveBeenCalled();
});

test('RegisterModal uses i18n translation for completeMessage', () => {
  const i18nSpy = jest.fn((key, fallback) => `translated: ${fallback}`);
  window.ss.i18n._t = i18nSpy;
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        RegisterComponent: ({ completeMessage: msg }) => (
          <div data-testid="register-component" data-complete-msg={msg}>
            Test
          </div>
        ),
      })}
    />
  );
  expect(i18nSpy).toHaveBeenCalledWith(
    'MultiFactorAuthentication.ADMIN_SETUP_COMPLETE_CONTINUE',
    expect.any(String)
  );
});

test('RegisterModal registers method with correct urlSegment', () => {
  const onAddRegisteredMethod = jest.fn();
  const onSetDefaultMethod = jest.fn();
  render(
    <RegisterModal
      {...makeProps({
        isOpen: true,
        registrationScreen: 2,
        registeredMethods: [],
        onAddRegisteredMethod,
        onSetDefaultMethod,
      })}
    />
  );
  const registerBtn = screen.getByTestId('register-btn');
  fireEvent.click(registerBtn);
  expect(onAddRegisteredMethod).toHaveBeenCalledWith(
    expect.objectContaining({
      urlSegment: 'test-method',
    })
  );
  expect(onSetDefaultMethod).toHaveBeenCalledWith('test-method');
});
