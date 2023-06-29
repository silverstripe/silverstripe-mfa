/* global jest, test, describe, it, expect */

import React from 'react';
import { fireEvent, render } from '@testing-library/react';
import { Component as Introduction, ActionList } from '../Introduction';

window.ss = {
  i18n: { _t: (key, string) => string },
};

function makeProps(obj = {}) {
  return {
    onContinue: () => {},
    TitleComponent: () => <div className="test-title" />,
    ...obj
  };
}

test('Introduction renders images when resource URLs are supplied', () => {
  const { container } = render(
    <Introduction {...makeProps({
      resources: {
        extra_factor_image_url: '/path/to/extra-factor.png',
        unique_image_url: '/unique.png',
      }
    })}
    />
  );
  const images = container.querySelectorAll('img.mfa-feature-list-item__icon');
  expect(images[0].getAttribute('src')).toBe('/path/to/extra-factor.png');
  expect(images[1].getAttribute('src')).toBe('/unique.png');
});

test('Introduction renders "find out more" link when user docs URL is supplied', () => {
  const { container } = render(
    <Introduction {...makeProps({
      resources: {
        user_help_link: '/help-link',
      }
    })}
    />
  );
  expect(container.querySelector('.mfa-feature-list-item__description a').getAttribute('href')).toBe('/help-link');
});

test('ActionList does not render a skip button by default', () => {
  const { container } = render(
    <ActionList />
  );
  expect(container.querySelector('.btn-primary').textContent).toBe('Get started');
});

test('ActionList triggers the continue handler when the continue action is clicked', () => {
  const onContinue = jest.fn();
  const { container } = render(
    <ActionList {...{
      onContinue
    }}
    />
  );
  fireEvent.click(container.querySelector('.btn-primary'));
  expect(onContinue).toHaveBeenCalled();
});

test('ActionList renders a skip button when supplied', () => {
  const { container } = render(
    <ActionList {...{
      canSkip: true
    }}
    />
  );
  expect(container.querySelector('.btn-secondary').textContent).toBe('Setup later');
});

test('ActionList triggers the skip handler when the skip action is clicked', () => {
  const onSkip = jest.fn();
  const { container } = render(
    <ActionList {...{
      canSkip: true,
      onSkip
    }}
    />
  );
  fireEvent.click(container.querySelector('.btn-secondary'));
  expect(onSkip).toHaveBeenCalled();
});
