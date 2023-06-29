/* global jest, test, expect */

// eslint-disable-next-line no-unused-vars
import React from 'react';
import { render, fireEvent } from '@testing-library/react';
import { Component as SelectMethod } from '../SelectMethod';

window.ss = {
  i18n: {
    _t: (key, string) => string,
    inject: (string, map) => Object.entries(map).reduce(
      (acc, [key, value]) => acc.replace(key, value),
      string
    ),
  },
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

function makeProps(obj = {}) {
  return {
    isAvailable: () => true,
    onClickBack: () => null,
    onSelectMethod: () => null,
    getUnavailableMessage: () => '',
    methods: [firstMethod, secondMethod],
    ...obj
  };
}

test('Verify renderControls() shows a back button that takes you back', () => {
  const onClickBack = jest.fn();
  const { container } = render(
    <SelectMethod {...makeProps({
      onClickBack,
    })}
    />
  );
  expect(container.querySelectorAll('.mfa-verify-select-method__actions')).toHaveLength(1);
  expect(container.querySelectorAll('.mfa-verify-select-method__back')).toHaveLength(1);
  fireEvent.click(container.querySelector('.mfa-verify-select-method__back'));
  expect(onClickBack).toHaveBeenCalled();
});

test('Verify renderMethod() shows methods as unavailable', () => {
  const { container } = render(
    <SelectMethod {...makeProps({
      isAvailable: () => false,
      getUnavailableMessage: () => 'Browser does not support it',
    })}
    />
  );
  expect(container.querySelectorAll('.mfa-verify-select-method__method')).toHaveLength(2);
  expect(container.querySelectorAll('.mfa-verify-select-method__method--unavailable')).toHaveLength(2);
  expect(container.querySelectorAll('.mfa-verify-select-method__method--available')).toHaveLength(0);
  expect(container.querySelectorAll('.mfa-verify-select-method__method-message')[0].textContent).toContain('Browser does not support it');
});

test('Verify renderMethod() shows methods as available', () => {
  const { container } = render(
    <SelectMethod {...makeProps({
      isAvailable: () => true,
    })}
    />
  );
  expect(container.querySelectorAll('.mfa-verify-select-method__method--unavailable')).toHaveLength(0);
  const methods = container.querySelectorAll('.mfa-verify-select-method__method');
  expect(methods).toHaveLength(2);
  expect(methods[0].textContent).toBe('Verify with {aye}');
  expect(methods[1].textContent).toBe('Verify with {bee}');
});

test('Verify renderMethod() trigggers click handler when clicking a method', () => {
  const onSelectMethod = jest.fn();
  const { container } = render(
    <SelectMethod {...makeProps({
      onSelectMethod,
    })}
    />
  );
  const method = container.querySelectorAll('.mfa-verify-select-method__method')[0];
  fireEvent.click(method);
  expect(onSelectMethod).toHaveBeenCalledWith({
    component: 'TestRegistration',
    description: 'Register using aye',
    name: 'Aye',
    supportLink: 'https://google.com',
    urlSegment: 'aye',
  });
});

test('Verify render() renders an image', () => {
  const { container } = render(
    <SelectMethod {...makeProps({
      resources: {
        more_options_image_url: '/foo.jpg',
      }
    })}
    />
  );
  expect(container.querySelectorAll('.mfa-verify-select-method__image')).toHaveLength(1);
});
