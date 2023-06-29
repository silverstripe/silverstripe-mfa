/* global jest, test, describe, it, expect */

import React from 'react';
import { render, fireEvent } from '@testing-library/react';
import { Component as SelectMethod } from '../SelectMethod';

window.ss = {
  i18n: { _t: (key, string) => string },
};

function makeProps(obj = {}) {
  return {
    methods: [
      {
        urlSegment: 'aye',
        name: 'Aye',
        description: 'Register using aye',
        supportLink: 'https://google.com',
        component: 'Test',
      },
      {
        urlSegment: 'bee',
        name: 'Bee',
        description: 'Register using bee',
        supportLink: 'https://foo.test',
        component: 'Test',
      },
    ],
    isAvailable: () => true,
    onClickBack: () => null,
    onSelectMethod: () => null,
    TitleComponent: () => <div className="test-title" />,
    MethodTileComponent: ({ method, onClick }) => <div className="test-method-tile" data-method={method.urlSegment} onClick={onClick} />,
    ...obj
  };
}

test('SelectMethod automatically selects the only available method', () => {
  const onSelectMethod = jest.fn();
  render(
    <SelectMethod {...makeProps({
      methods: [
        makeProps().methods[0]
      ],
      onSelectMethod
    })}
    />
  );
  expect(onSelectMethod).toHaveBeenCalled();
});

test('SelectMethod does not automatically select the only available method when not usable', () => {
  const onSelectMethod = jest.fn();
  render(
    <SelectMethod {...makeProps({
      onSelectMethod,
      methods: [
        makeProps().methods[0]
      ],
      isAvailable: () => false
    })}
    />
  );
  expect(onSelectMethod).not.toHaveBeenCalled();
});

test('SelectMethod passes the highlighted method to the onSelectMethod handler', async () => {
  const onSelectMethod = jest.fn();
  const { container } = render(
    <SelectMethod {...makeProps({
      onSelectMethod
    })}
    />
  );
  fireEvent.click(container.querySelector('[data-method="bee"]'));
  fireEvent.click(container.querySelector('.mfa-action-list__item .btn-primary'));
  expect(onSelectMethod).toHaveBeenCalledWith({
    component: 'Test',
    description: 'Register using bee',
    name: 'Bee',
    supportLink: 'https://foo.test',
    urlSegment: 'bee'
  });
});

test('SelectMethod clicking the back button triggers the onClickBack callback', () => {
  const onClickBack = jest.fn();
  const { container } = render(
    <SelectMethod {...makeProps({
      onClickBack
    })}
    />
  );
  fireEvent.click(container.querySelector('.mfa-action-list__item .btn-secondary'));
  expect(onClickBack).toHaveBeenCalled();
});

test('SelectMethod renders a "Next" button', () => {
  const { container } = render(<SelectMethod {...makeProps()}/>);
  expect(container.querySelector('.mfa-action-list .btn-primary').textContent).toBe('Next');
});

test('SelectMethod renders a "Next" button in a disabled state when no method is highlighted', () => {
  const { container } = render(<SelectMethod {...makeProps()}/>);
  expect(container.querySelector('.mfa-action-list .btn-primary').disabled).toBe(true);
});

test('SelectMethod renders an active "Next" button when a method is highlighted', () => {
  const { container } = render(<SelectMethod {...makeProps()}/>);
  fireEvent.click(container.querySelector('[data-method="bee"]'));
  expect(container.querySelector('.mfa-action-list .btn-primary').disabled).toBe(false);
});

test('SelectMethod renders a "Back" button', () => {
  const { container } = render(<SelectMethod {...makeProps()}/>);
  expect(container.querySelector('.mfa-action-list .btn-secondary').textContent).toBe('Back');
});

test('SelectMethod renders a MethodTile component for each available method', () => {
  const { container } = render(<SelectMethod {...makeProps()}/>);
  const methodTiles = container.querySelectorAll('.test-method-tile');
  expect(methodTiles).toHaveLength(2);
  expect(methodTiles[0].getAttribute('data-method')).toBe('aye');
  expect(methodTiles[1].getAttribute('data-method')).toBe('bee');
});
