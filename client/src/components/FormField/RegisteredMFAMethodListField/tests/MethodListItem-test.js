/* global jest, test, describe, it, expect */
import React from 'react';
import { render } from '@testing-library/react';
import MethodListItem from '../MethodListItem';

window.ss = {
  i18n: {
    _t: (key, string) => string,
    detectLocale: () => 'en_NZ',
    inject: (message) => message, // not a great mock...
  },
};

function makeProps(obj = {}) {
  return {
    method: {
      urlSegment: 'foo'
    },
    RemoveComponent: () => <div className="test-remove" />,
    SetDefaultComponent: () => <div className="test-set-default" />,
    ...obj
  };
}

test('MethodListitem identifies default methods', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      isDefaultMethod: true
    })}
    />
  );
  expect(container.querySelector('.registered-method-list-item').textContent).toBe('{method} (default): Registered');
});

test('MethodListItem identifies backup methods', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      isBackupMethod: true
    })}
    />
  );
  expect(container.querySelector('.registered-method-list-item').textContent).toBe('{method}: Created {date}');
});

test('MethodListItem does not render remove buttons by default', () => {
  const { container } = render(
    <MethodListItem {...makeProps()}/>
  );
  expect(container.querySelector('.test-remove')).toBeNull();
});

test('MethodListItem does render remove buttons if canRemove is true', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      canRemove: true,
    })}
    />
  );
  expect(container.querySelector('.test-remove')).not.toBeNull();
});
