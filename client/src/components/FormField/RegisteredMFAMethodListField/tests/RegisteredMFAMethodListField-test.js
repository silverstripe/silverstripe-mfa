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
