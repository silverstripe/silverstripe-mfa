/* global jest, test, describe, it, expect */

import React from 'react';
import { render } from '@testing-library/react';
import { Component as RegisteredMFAMethodListField } from '../RegisteredMFAMethodListField';

import translationStrings from '../../../../../lang/src/en.json';

window.ss = {
  i18n: { _t: (key, string) => string, detectLocale: () => 'en', inject: () => {} },
};

const altMethod = { name: 'Alt Method', urlSegment: 'altmethod', component: '' };
const backupMethod = { name: 'Backup Method', urlSegment: 'backup', component: '' };
const defaultMethod = { name: 'Default Method', urlSegment: 'default', component: '' };

function makeProps(obj = {}) {
  return {
    defaultMethod: 'default',
    backupMethod,
    availableMethods: [],
    registeredMethods: [],
    RegisterModalComponent: () => <div className="test-register-modal" />,
    MethodListItemComponent: ({ method }) => <div className="test-method-list-item" title={method.urlSegment} />,
    onUpdateAvailableMethods: () => {},
    onSetRegisteredMethods: () => {},
    onSetDefaultMethod: () => {},
    ...obj
  };
}

test('RegisteredMFAMethodListField filters out backup methods', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [altMethod, backupMethod, defaultMethod]
    })}
    />
  );
  const methods = container.querySelectorAll('.method-list .test-method-list-item');
  expect(methods).toHaveLength(2);
  expect(methods[0].getAttribute('title')).toBe('altmethod');
  expect(methods[1].getAttribute('title')).toBe('default');
});

test('RegisteredMFAMethodListField renders a button when there are available methods', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      availableMethods: [altMethod]
    })}
    />
  );
  expect(container.querySelector('.registered-mfa-method-list-field__button')).not.toBeNull();
});

test('RegisteredMFAMethodListField does not render a button when there are no available methods', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      availableMethods: []
    })}
    />
  );
  expect(container.querySelector('.registered-mfa-method-list-field__button')).toBeNull();
});

test('RegisteredMFAMethodListField doesn\'t render a button in read-only mode', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      availableMethods: [altMethod],
      readOnly: true
    })}
    />
  );
  expect(container.querySelector('.registered-mfa-method-list-field__button')).toBeNull();
});

test('RegisteredMFAMethodListField renders a button with the correct label', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      availableMethods: [altMethod]
    })}
    />
  );
  expect(container.querySelector('.registered-mfa-method-list-field__button').textContent).toBe(translationStrings['MultiFactorAuthentication.ADD_FIRST_METHOD']);
});

test('RegisteredMFAMethodListField renders a button with the correct label when there are already registered methods', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      availableMethods: [altMethod],
      registeredMethods: [defaultMethod]
    })}
    />
  );
  expect(container.querySelector('.registered-mfa-method-list-field__button').textContent).toBe(translationStrings['MultiFactorAuthentication.ADD_ANOTHER_METHOD']);
});

test('RegisteredMFAMethodListField renders the read-only view when readOnly is passed', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [altMethod],
      readOnly: true
    })}
    />
  );
  expect(container.querySelector('.registered-mfa-method-list-field--read-only')).not.toBeNull();
});

test('RegisteredMFAMethodListField renders backup method when provided', () => {
  const calls = [];
  const MockMethodListItem = (props) => {
    calls.push(props);
    return (
      <div data-testid={props.isBackupMethod ? 'backup-method' : 'method-item'} />
    );
  };
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [altMethod, backupMethod, defaultMethod],
      backupMethod,
      MethodListItemComponent: MockMethodListItem
    })}
    />
  );
  const backupItem = container.querySelector('[data-testid="backup-method"]');
  expect(backupItem).not.toBeNull();
  const backupCall = calls.find(props => props.isBackupMethod);
  expect(backupCall).toBeDefined();
});

test('RegisteredMFAMethodListField does not render backup method when not in registered methods', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [altMethod, defaultMethod],
      backupMethod
    })}
    />
  );
  const backupItems = container.querySelectorAll('.registered-method-list-item--backup');
  expect(backupItems).toHaveLength(0);
});

test('RegisteredMFAMethodListField renders no methods message when no methods are registered', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: []
    })}
    />
  );
  const noMethodsMsg = container.querySelector('.registered-mfa-method-list-field__no-methods');
  expect(noMethodsMsg).not.toBeNull();
  expect(noMethodsMsg.textContent).toBe(translationStrings['MultiFactorAuthentication.NO_METHODS_REGISTERED']);
});

test('RegisteredMFAMethodListField renders read-only no methods message', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [],
      readOnly: true
    })}
    />
  );
  const noMethodsMsg = container.querySelector('.registered-mfa-method-list-field__no-methods');
  expect(noMethodsMsg).not.toBeNull();
  expect(noMethodsMsg.textContent).toBe(translationStrings['MultiFactorAuthentication.NO_METHODS_REGISTERED_READONLY']);
});

test('RegisteredMFAMethodListField does not render no methods message when methods are registered', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [altMethod]
    })}
    />
  );
  const noMethodsMsg = container.querySelector('.registered-mfa-method-list-field__no-methods');
  expect(noMethodsMsg).toBeNull();
});

test('RegisteredMFAMethodListField filters out backup method from base methods list', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [backupMethod],
      backupMethod
    })}
    />
  );
  const noMethodsMsg = container.querySelector('.registered-mfa-method-list-field__no-methods');
  expect(noMethodsMsg).not.toBeNull();
});

test('RegisteredMFAMethodListField renders AccountResetUI in read-only mode', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      readOnly: true,
      resetEndpoint: 'http://example.com/reset'
    })}
    />
  );
  expect(container.querySelector('hr')).not.toBeNull();
});

test('RegisteredMFAMethodListField does not render AccountResetUI when not read-only', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      readOnly: false,
      resetEndpoint: 'http://example.com/reset'
    })}
    />
  );
  expect(container.querySelector('hr')).toBeNull();
});

test('RegisteredMFAMethodListField renders RegisterModal', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      availableMethods: [altMethod]
    })}
    />
  );
  expect(container.querySelector('.test-register-modal')).not.toBeNull();
});

test('RegisteredMFAMethodListField passes correct props to MethodListItem for default method', () => {
  const MockMethodListItem = ({ method, isDefaultMethod }) => (
    <div data-testid="method-item" data-is-default={isDefaultMethod} title={method.urlSegment} />
  );
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [altMethod, defaultMethod],
      defaultMethod: 'default',
      MethodListItemComponent: MockMethodListItem
    })}
    />
  );
  const methodItems = container.querySelectorAll('[data-testid="method-item"]');
  expect(methodItems).toHaveLength(2);
  const defaultItem = Array.from(methodItems).find(item => item.getAttribute('title') === 'default');
  expect(defaultItem.getAttribute('data-is-default')).toBe('true');
});

test('RegisteredMFAMethodListField prevents removal of last base method when isMFARequired is true', () => {
  const MockMethodListItem = ({ canRemove }) => (
    <div data-testid="method-item" data-can-remove={canRemove} />
  );
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [altMethod],
      isMFARequired: true,
      MethodListItemComponent: MockMethodListItem
    })}
    />
  );
  const methodItem = container.querySelector('[data-testid="method-item"]');
  expect(methodItem.getAttribute('data-can-remove')).toBe('false');
});

test('RegisteredMFAMethodListField allows removal of last base method when isMFARequired is false', () => {
  const MockMethodListItem = ({ canRemove }) => (
    <div data-testid="method-item" data-can-remove={canRemove} />
  );
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [altMethod],
      isMFARequired: false,
      MethodListItemComponent: MockMethodListItem
    })}
    />
  );
  const methodItem = container.querySelector('[data-testid="method-item"]');
  expect(methodItem.getAttribute('data-can-remove')).toBe('true');
});

test('RegisteredMFAMethodListField allows removal of last base method when there are multiple methods', () => {
  const MockMethodListItem = ({ canRemove }) => (
    <div data-testid="method-item" data-can-remove={canRemove} />
  );
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [altMethod, defaultMethod],
      isMFARequired: true,
      MethodListItemComponent: MockMethodListItem
    })}
    />
  );
  const methodItems = container.querySelectorAll('[data-testid="method-item"]');
  methodItems.forEach(item => {
    expect(item.getAttribute('data-can-remove')).toBe('true');
  });
});

test('RegisteredMFAMethodListField passes canReset to MethodListItem when not read-only', () => {
  const MockMethodListItem = ({ canReset }) => (
    <div data-testid="method-item" data-can-reset={canReset} />
  );
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [altMethod],
      readOnly: false,
      MethodListItemComponent: MockMethodListItem
    })}
    />
  );
  const methodItem = container.querySelector('[data-testid="method-item"]');
  expect(methodItem.getAttribute('data-can-reset')).toBe('true');
});

test('RegisteredMFAMethodListField prevents reset when read-only', () => {
  const MockMethodListItem = ({ canReset }) => (
    <div data-testid="method-item" data-can-reset={canReset} />
  );
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [altMethod],
      readOnly: true,
      MethodListItemComponent: MockMethodListItem
    })}
    />
  );
  const methodItem = container.querySelector('[data-testid="method-item"]');
  expect(methodItem.getAttribute('data-can-reset')).toBe('false');
});

test('RegisteredMFAMethodListField passes correct backup method createdDate to MethodListItem', () => {
  const mockDate = '2023-11-17';
  const MockMethodListItem = ({ createdDate, isBackupMethod }) => (
    <div data-testid="backup-item" data-created-date={createdDate} data-is-backup={isBackupMethod} />
  );
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [backupMethod],
      backupMethod,
      backupCreatedDate: mockDate,
      MethodListItemComponent: MockMethodListItem
    })}
    />
  );
  const backupItem = container.querySelector('[data-testid="backup-item"]');
  expect(backupItem.getAttribute('data-created-date')).toBe(mockDate);
  expect(backupItem.getAttribute('data-is-backup')).toBe('true');
});

test('RegisteredMFAMethodListField renders with correct base CSS class', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps()} />
  );
  expect(container.querySelector('.registered-mfa-method-list-field')).not.toBeNull();
});

test('RegisteredMFAMethodListField renders method list with correct class', () => {
  const { container } = render(
    <RegisteredMFAMethodListField {...makeProps({
      registeredMethods: [altMethod]
    })}
    />
  );
  expect(container.querySelector('.method-list')).not.toBeNull();
});
