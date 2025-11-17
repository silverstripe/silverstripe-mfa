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

test('MethodTile does not attach active state when not active', () => {
  const { container } = render(
    <MethodTile {...makeProps({
      isActive: false
    })}
    />
  );
  expect(container.querySelector('.mfa-method-tile--active')).toBeNull();
});

test('MethodTile applies unsupported class when method is not available', () => {
  const { container } = render(
    <MethodTile {...makeProps({
      method: {
        ...makeProps().method,
        isAvailable: false
      }
    })}
    />
  );
  expect(container.querySelector('.mfa-method-tile--unsupported')).not.toBeNull();
});

test('MethodTile does not apply unsupported class when method is available', () => {
  const { container } = render(
    <MethodTile {...makeProps()}/>
  );
  expect(container.querySelector('.mfa-method-tile--unsupported')).toBeNull();
});

test('MethodTile ignores non-enter keys on keyup', () => {
  const onClick = jest.fn();
  const { container } = render(
    <MethodTile {...makeProps({
      onClick
    })}
    />
  );
  fireEvent.keyUp(container.querySelector('.mfa-method-tile__content'), { keyCode: 32 });
  expect(onClick).not.toHaveBeenCalled();
});

test('MethodTile does not trigger click handler on enter key when method is unavailable', () => {
  const onClick = jest.fn();
  const { container } = render(
    <MethodTile {...makeProps({
      onClick,
      method: {
        ...makeProps().method,
        isAvailable: false
      }
    })}
    />
  );
  fireEvent.keyUp(container.querySelector('.mfa-method-tile__content'), { keyCode: 13 });
  expect(onClick).not.toHaveBeenCalled();
});

test('MethodTile renders thumbnail image when present', () => {
  const { container } = render(
    <MethodTile {...makeProps({
      method: {
        ...makeProps().method,
        thumbnail: 'https://example.com/image.png'
      }
    })}
    />
  );
  const img = container.querySelector('.mfa-method-tile__thumbnail');
  expect(img).not.toBeNull();
  expect(img.getAttribute('src')).toBe('https://example.com/image.png');
  expect(img.getAttribute('alt')).toBe('Aye');
});

test('MethodTile does not render thumbnail when not present', () => {
  const { container } = render(
    <MethodTile {...makeProps({
      method: {
        ...makeProps().method,
        thumbnail: null
      }
    })}
    />
  );
  expect(container.querySelector('.mfa-method-tile__thumbnail')).toBeNull();
});

test('MethodTile renders description text', () => {
  const { container } = render(
    <MethodTile {...makeProps()}/>
  );
  expect(container.querySelector('.mfa-method-tile__description').textContent).toContain('Register using aye');
});

test('MethodTile handles missing description gracefully', () => {
  const { container } = render(
    <MethodTile {...makeProps({
      method: {
        ...makeProps().method,
        description: null
      }
    })}
    />
  );
  const desc = container.querySelector('.mfa-method-tile__description');
  expect(desc).not.toBeNull();
});

test('MethodTile renders support link with correct attributes', () => {
  const { container } = render(
    <MethodTile {...makeProps()}/>
  );
  const link = container.querySelector('.mfa-method-tile__support-link');
  expect(link).not.toBeNull();
  expect(link.getAttribute('href')).toBe('https://google.com');
  expect(link.getAttribute('target')).toBe('_blank');
  expect(link.getAttribute('rel')).toBe('noopener noreferrer');
});

test('MethodTile does not render support link when not present', () => {
  const { container } = render(
    <MethodTile {...makeProps({
      method: {
        ...makeProps().method,
        supportLink: null
      }
    })}
    />
  );
  expect(container.querySelector('.mfa-method-tile__support-link')).toBeNull();
});

test('MethodTile uses custom support text when provided', () => {
  const { container } = render(
    <MethodTile {...makeProps({
      method: {
        ...makeProps().method,
        supportText: 'Custom help text'
      }
    })}
    />
  );
  const link = container.querySelector('.mfa-method-tile__support-link');
  expect(link.textContent).toBe('Custom help text');
});

test('MethodTile uses default support text when custom text not provided', () => {
  const { container } = render(
    <MethodTile {...makeProps({
      method: {
        ...makeProps().method,
        supportText: undefined
      }
    })}
    />
  );
  const link = container.querySelector('.mfa-method-tile__support-link');
  expect(link.textContent).toBe('Find out more.');
});

test('MethodTile renders correct title with method name', () => {
  window.ss.i18n.inject = (string, replacements) => {
    let result = string;
    Object.keys(replacements).forEach(key => {
      result = result.replace(`{${key}}`, replacements[key]);
    });
    return result;
  };
  const { container } = render(
    <MethodTile {...makeProps({
      method: {
        ...makeProps().method,
        name: 'TestMethod'
      }
    })}
    />
  );
  const title = container.querySelector('.mfa-method-tile__title');
  expect(title.textContent).toContain('testmethod');
});

test('MethodTile renders unavailable message when method not available', () => {
  const { container } = render(
    <MethodTile {...makeProps({
      isAvailable: () => false,
      getUnavailableMessage: () => 'Browser not supported'
    })}
    />
  );
  const unavailableTitle = container.querySelector('.mfa-method-tile__unavailable-title');
  expect(unavailableTitle).not.toBeNull();
  expect(unavailableTitle.textContent).toBe('Unsupported: ');
});

test('MethodTile does not render unavailable text when message is empty', () => {
  const { container } = render(
    <MethodTile {...makeProps({
      isAvailable: () => false,
      getUnavailableMessage: () => ''
    })}
    />
  );
  expect(container.querySelector('.mfa-method-tile__unavailable-text')).toBeNull();
});
