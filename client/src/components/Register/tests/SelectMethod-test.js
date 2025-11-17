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

test('SelectMethod renders the Title component when showTitle is true', () => {
  const { container } = render(<SelectMethod {...makeProps({ showTitle: true })}/>);
  expect(container.querySelector('.test-title')).not.toBeNull();
});

test('SelectMethod does not render the Title component when showTitle is false', () => {
  const { container } = render(<SelectMethod {...makeProps({ showTitle: false })}/>);
  expect(container.querySelector('.test-title')).toBeNull();
});

test('SelectMethod renders with three-column layout when methods count is divisible by 3', () => {
  const { container } = render(
    <SelectMethod {...makeProps({
      methods: [
        makeProps().methods[0],
        makeProps().methods[1],
        { urlSegment: 'cee', name: 'Cee', description: 'Register using cee', component: 'Test' }
      ]
    })}
    />
  );
  const methodGroup = container.querySelector('.mfa-method-tile-group');
  expect(methodGroup.classList.contains('mfa-method-tile-group--three-columns')).toBe(true);
});

test('SelectMethod does not render three-column layout when methods count is not divisible by 3', () => {
  const { container } = render(
    <SelectMethod {...makeProps({
      methods: [
        makeProps().methods[0],
        makeProps().methods[1]
      ]
    })}
    />
  );
  const methodGroup = container.querySelector('.mfa-method-tile-group');
  expect(methodGroup.classList.contains('mfa-method-tile-group--three-columns')).toBe(false);
});

test('SelectMethod allows switching highlighted method by clicking different tiles', () => {
  const { container } = render(<SelectMethod {...makeProps()}/>);
  const nextButton = container.querySelector('.mfa-action-list .btn-primary');

  fireEvent.click(container.querySelector('[data-method="aye"]'));
  expect(nextButton.disabled).toBe(false);

  fireEvent.click(container.querySelector('[data-method="bee"]'));
  expect(nextButton.disabled).toBe(false);
});

test('SelectMethod correctly updates state when a method tile is clicked', () => {
  const { container } = render(<SelectMethod {...makeProps()} />);
  const nextButton = container.querySelector('.mfa-action-list .btn-primary');
  expect(nextButton.disabled).toBe(true);
  fireEvent.click(container.querySelector('[data-method="bee"]'));
  expect(nextButton.disabled).toBe(false);
  fireEvent.click(container.querySelector('[data-method="aye"]'));
  expect(nextButton.disabled).toBe(false);
});

test('SelectMethod handles onClickBack when it is not provided', () => {
  const { container } = render(
    <SelectMethod {...makeProps({
      onClickBack: undefined
    })}
    />
  );
  expect(() => {
    fireEvent.click(container.querySelector('.mfa-action-list__item .btn-secondary'));
  }).not.toThrow();
});

test('SelectMethod renders with empty methods array', () => {
  const { container } = render(
    <SelectMethod {...makeProps({
      methods: []
    })}
    />
  );
  const methodTiles = container.querySelectorAll('.test-method-tile');
  expect(methodTiles).toHaveLength(0);
  expect(container.querySelector('.mfa-method-tile-group')).not.toBeNull();
});

test('SelectMethod does not automatically select a method when isAvailable is not a function', () => {
  const onSelectMethod = jest.fn();
  render(
    <SelectMethod {...makeProps({
      methods: [makeProps().methods[0]],
      isAvailable: undefined,
      onSelectMethod
    })}
    />
  );
  expect(onSelectMethod).not.toHaveBeenCalled();
});

test('SelectMethod calls onClickBack without any arguments', () => {
  const onClickBack = jest.fn();
  const { container } = render(
    <SelectMethod {...makeProps({
      onClickBack
    })}
    />
  );
  fireEvent.click(container.querySelector('.mfa-action-list__item .btn-secondary'));
  expect(onClickBack).toHaveBeenCalledWith();
});
