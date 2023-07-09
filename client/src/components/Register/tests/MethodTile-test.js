/* global jest, test, describe, it, expect */

import React from 'react';
import { fireEvent, render } from '@testing-library/react';
import { Component as MethodTile } from '../MethodTile';

function makeProps(obj = {}) {
  return {
    method: {
      urlSegment: 'aye',
      name: 'Aye',
      description: 'Register using aye',
      supportLink: 'https://google.com',
      component: 'Test',
      isAvailable: true
    },
    onClick: () => {},
    isAvailable: () => true,
    getUnavailableMessage: () => {},
    ...obj
  };
}

window.ss = {
  i18n: {
    _t: (key, string) => string,
    inject: string => string,
  },
};

test('MethodTile passes click to handler prop if method is available', () => {
  const onClick = jest.fn();
  const { container } = render(
    <MethodTile {...makeProps({
      onClick
    })}
    />
  );
  fireEvent.click(container.querySelector('.mfa-method-tile__content'));
  expect(onClick).toHaveBeenCalled();
});

test('MethodTile click doesn\t do anything when method not available', () => {
  const onClick = jest.fn();
  const { container } = render(
    <MethodTile {...makeProps({
      method: {
        ...makeProps().method,
        isAvailable: false
      }
    })}
    />
  );
  fireEvent.click(container.querySelector('.mfa-method-tile__content'));
  expect(onClick).not.toHaveBeenCalled();
});

test('MethodTile renders does not render a mask when is available', () => {
  const { container } = render(
    <MethodTile {...makeProps()}/>
  );
  expect(container.querySelector('.mfa-method-tile__unavailable-mask')).toBeNull();
});

test('MethodTile renders a mask when is not available', () => {
  const { container } = render(
    <MethodTile {...makeProps({
      isAvailable: () => false,
      getUnavailableMessage: () => 'Test message here'
    })}
    />
  );
  expect(container.querySelector('.mfa-method-tile__unavailable-mask').textContent).toBe('Unsupported: Test message here');
});

test('MethodTile treats the enter key as a click', () => {
  const onClick = jest.fn();
  const { container } = render(
    <MethodTile {...makeProps({
      onClick
    })}
    />
  );
  fireEvent.keyUp(container.querySelector('.mfa-method-tile__content'), { keyCode: 13 });
  expect(onClick).toHaveBeenCalled();
});

test('MethodTile attaches an active state when active', () => {
  const { container } = render(
    <MethodTile {...makeProps({
      isActive: true
    })}
    />
  );
  expect(container.querySelector('.mfa-method-tile--active')).not.toBeNull();
});
