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

test('Verify render() does not render an image when resources is not provided', () => {
  const { container } = render(
    <SelectMethod {...makeProps({
      resources: undefined,
    })}
    />
  );
  expect(container.querySelectorAll('.mfa-verify-select-method__image')).toHaveLength(0);
});

test('Verify render() does not render an image when more_options_image_url is not provided', () => {
  const { container } = render(
    <SelectMethod {...makeProps({
      resources: {},
    })}
    />
  );
  expect(container.querySelectorAll('.mfa-verify-select-method__image')).toHaveLength(0);
});

test('Verify render() renders the correct image src and alt text', () => {
  const { container } = render(
    <SelectMethod {...makeProps({
      resources: {
        more_options_image_url: '/path/to/image.png',
      }
    })}
    />
  );
  const image = container.querySelector('.mfa-verify-select-method__image');
  expect(image.src).toContain('/path/to/image.png');
  expect(image.getAttribute('alt')).toBe('Graphic depicting various MFA options');
  expect(image.getAttribute('aria-hidden')).toBe('true');
});

test('Verify render() renders the section title', () => {
  const { container } = render(
    <SelectMethod {...makeProps()} />
  );
  const title = container.querySelector('.mfa-section-title');
  expect(title).not.toBeNull();
  expect(title.textContent).toBe('Try another way to verify');
});

test('Verify render() renders the last resort message', () => {
  const { container } = render(
    <SelectMethod {...makeProps()} />
  );
  const message = container.querySelector('.mfa-verify-select-method__content p');
  expect(message).not.toBeNull();
  expect(message.textContent).toContain('Contact your site administrator');
});

test('Verify render() renders the main container structure', () => {
  const { container } = render(
    <SelectMethod {...makeProps()} />
  );
  expect(container.querySelectorAll('.mfa-verify-select-method')).toHaveLength(1);
  expect(container.querySelectorAll('.mfa-verify-select-method__container')).toHaveLength(1);
  expect(container.querySelectorAll('.mfa-verify-select-method__content')).toHaveLength(1);
});

test('Verify renderMethodList() renders empty list when no methods provided', () => {
  const { container } = render(
    <SelectMethod {...makeProps({
      methods: [],
    })}
    />
  );
  const methodList = container.querySelector('.mfa-verify-select-method__method-list');
  expect(methodList).not.toBeNull();
  expect(methodList.children).toHaveLength(0);
});

test('Verify renderMethod() shows support link for unavailable methods', () => {
  const methodWithLink = {
    urlSegment: 'test',
    name: 'Test Method',
    description: 'Test description',
    supportLink: 'https://help.example.com/test',
    component: 'TestComponent',
  };
  const { container } = render(
    <SelectMethod {...makeProps({
      isAvailable: () => false,
      methods: [methodWithLink],
      getUnavailableMessage: () => 'Not supported',
    })}
    />
  );
  const supportLink = container.querySelector('a[href="https://help.example.com/test"]');
  expect(supportLink).not.toBeNull();
  expect(supportLink.textContent).toBe('unavailable');
  expect(supportLink.getAttribute('target')).toBe('_blank');
  expect(supportLink.getAttribute('rel')).toBe('noreferrer noopener');
});

test('Verify renderMethod() does not show support link when unavailable but no link provided', () => {
  const methodWithoutLink = {
    urlSegment: 'test',
    name: 'Test Method',
    description: 'Test description',
    supportLink: '',
    component: 'TestComponent',
  };
  const { container } = render(
    <SelectMethod {...makeProps({
      isAvailable: () => false,
      methods: [methodWithoutLink],
      getUnavailableMessage: () => 'Not supported',
    })}
    />
  );
  expect(container.querySelectorAll('a[target="_blank"]')).toHaveLength(0);
});

test('Verify renderMethod() does not show message when getUnavailableMessage returns empty string', () => {
  const { container } = render(
    <SelectMethod {...makeProps({
      isAvailable: () => false,
      methods: [firstMethod],
      getUnavailableMessage: () => '',
    })}
    />
  );
  expect(container.querySelectorAll('.mfa-verify-select-method__method-message')).toHaveLength(0);
});

test('Verify renderMethod() shows message when getUnavailableMessage returns a string', () => {
  const customMessage = 'Custom unavailability message';
  const { container } = render(
    <SelectMethod {...makeProps({
      isAvailable: () => false,
      methods: [firstMethod],
      getUnavailableMessage: () => customMessage,
    })}
    />
  );
  const messageElements = container.querySelectorAll('.mfa-verify-select-method__method-message');
  expect(messageElements).toHaveLength(1);
  expect(messageElements[0].textContent).toBe(customMessage);
});

test('Verify renderControls() back button prevents default behavior', () => {
  const onClickBack = jest.fn();
  const { container } = render(
    <SelectMethod {...makeProps({
      onClickBack,
    })}
    />
  );
  const backButton = container.querySelector('.mfa-verify-select-method__back');
  const event = new MouseEvent('click', { bubbles: true });
  event.preventDefault = jest.fn();
  fireEvent.click(backButton, event);
  expect(onClickBack).toHaveBeenCalled();
});

test('Verify renderMethod() method links prevent default behavior', () => {
  const onSelectMethod = jest.fn(() => (e) => {
    if (e) e.preventDefault();
  });
  const { container } = render(
    <SelectMethod {...makeProps({
      isAvailable: () => true,
      onSelectMethod,
    })}
    />
  );
  const methodLink = container.querySelector('.mfa-verify-select-method__method a');
  fireEvent.click(methodLink);
  expect(onSelectMethod).toHaveBeenCalled();
});

test('Verify multiple available and unavailable methods together', () => {
  const methods = [
    {
      urlSegment: 'available1',
      name: 'Available Method 1',
      description: 'First available',
      supportLink: '',
      component: 'Test',
    },
    {
      urlSegment: 'unavailable1',
      name: 'Unavailable Method',
      description: 'Not available',
      supportLink: 'https://example.com/help',
      component: 'Test',
    },
    {
      urlSegment: 'available2',
      name: 'Available Method 2',
      description: 'Second available',
      supportLink: '',
      component: 'Test',
    },
  ];
  const { container } = render(
    <SelectMethod {...makeProps({
      isAvailable: (method) => method.urlSegment !== 'unavailable1',
      methods,
      getUnavailableMessage: () => 'Not available in your browser',
    })}
    />
  );
  const totalMethods = container.querySelectorAll('.mfa-verify-select-method__method');
  const unavailableMethods = container.querySelectorAll('.mfa-verify-select-method__method--unavailable');
  expect(totalMethods).toHaveLength(3);
  expect(unavailableMethods).toHaveLength(1);
});

test('Verify renderMethod() correctly injects method name into lead-in label', () => {
  const customMethod = {
    urlSegment: 'custom',
    name: 'CustomMethod',
    description: 'Custom method',
    supportLink: '',
    component: 'Test',
  };
  const { container } = render(
    <SelectMethod {...makeProps({
      isAvailable: () => true,
      methods: [customMethod],
    })}
    />
  );
  const methodText = container.querySelector('.mfa-verify-select-method__method a').textContent;
  expect(methodText).toContain('custommethod');
});
