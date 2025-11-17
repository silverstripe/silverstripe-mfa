/* global jest, test, expect */

import React from 'react';
import { render, fireEvent, screen } from '@testing-library/react';
import Verify from '../Verify';

window.ss = {
  i18n: { _t: (key, string) => string },
};

function makeProps(obj = {}) {
  return {
    onCompleteVerification: jest.fn(),
    ...obj
  };
}

test('Verify has a disabled button on load', () => {
  const { container } = render(<Verify {...makeProps()} />);
  expect(container.querySelector('button.btn-primary').disabled).toBe(true);
});

test('Verify will un-disable the button when input is provided', () => {
  const { container } = render(<Verify {...makeProps()} />);
  const input = container.querySelector('input.mfa-verify-backup-codes__input');
  fireEvent.change(input, { target: { value: 'x' } });
  expect(container.querySelector('button.btn-primary').disabled).toBe(false);
});

test('Verify renders the given "more options control"', () => {
  const { container } = render(
    <Verify {...makeProps({
      moreOptionsControl: <div>More options!</div>
    })}
    />
  );
  expect(container.querySelectorAll('.mfa-action-list__item')[1].textContent).toBe('More options!');
});

test('Verify triggers login completion with the right value when the button is pressed', () => {
  const onCompleteVerification = jest.fn();
  const { container } = render(
    <Verify {...makeProps({
      onCompleteVerification
    })}
    />
  );
  const input = container.querySelector('input.mfa-verify-backup-codes__input');
  fireEvent.change(input, { target: { value: 'something' } });
  fireEvent.click(container.querySelector('button.btn-primary'));
  expect(onCompleteVerification).toBeCalledWith({ code: 'something' });
});

test('Verify re-disables button when input is cleared', () => {
  const { container } = render(<Verify {...makeProps()} />);
  const input = container.querySelector('input.mfa-verify-backup-codes__input');
  fireEvent.change(input, { target: { value: 'code' } });
  expect(container.querySelector('button.btn-primary').disabled).toBe(false);
  fireEvent.change(input, { target: { value: '' } });
  expect(container.querySelector('button.btn-primary').disabled).toBe(true);
});

test('Verify renders error message when error prop is provided', () => {
  const { container } = render(
    <Verify {...makeProps({
      error: 'Invalid code'
    })}
    />
  );
  expect(container.querySelector('.help-block').textContent).toBe('Invalid code');
});

test('Verify renders error state class when error prop is provided', () => {
  const { container } = render(
    <Verify {...makeProps({
      error: 'Invalid code'
    })}
    />
  );
  const formGroup = container.querySelector('.mfa-verify-backup-codes__input-container');
  expect(formGroup.classList.contains('has-error')).toBe(true);
});

test('Verify does not render error state class when error is not provided', () => {
  const { container } = render(<Verify {...makeProps()} />);
  const formGroup = container.querySelector('.mfa-verify-backup-codes__input-container');
  expect(formGroup.classList.contains('has-error')).toBe(false);
});

test('Verify renders description text', () => {
  render(<Verify {...makeProps()} />);
  const description = screen.getByText(/Use one of the recovery codes you received/);
  expect(description).not.toBeNull();
});

test('Verify renders support link when method with supportLink is provided', () => {
  render(
    <Verify {...makeProps({
      method: {
        supportLink: 'https://example.com/help'
      }
    })}
    />
  );
  const link = screen.getByText(/How to use recovery codes/);
  expect(link).not.toBeNull();
  expect(link.getAttribute('href')).toBe('https://example.com/help');
  expect(link.getAttribute('target')).toBe('_blank');
  expect(link.getAttribute('rel')).toBe('noopener noreferrer');
});

test('Verify does not render support link when method is not provided', () => {
  render(<Verify {...makeProps()} />);
  expect(screen.queryByText(/How to use recovery codes/)).toBeNull();
});

test('Verify does not render support link when supportLink is not provided', () => {
  render(
    <Verify {...makeProps({
      method: {}
    })}
    />
  );
  expect(screen.queryByText(/How to use recovery codes/)).toBeNull();
});

test('Verify renders input with correct attributes', () => {
  const { container } = render(<Verify {...makeProps()} />);
  const input = container.querySelector('input.mfa-verify-backup-codes__input');
  expect(input.type).toBe('text');
  expect(input.id).toBe('backup-code');
  expect(input.hasAttribute('placeholder')).toBe(true);
});

test('Verify renders input label', () => {
  render(<Verify {...makeProps()} />);
  const label = screen.getByLabelText(/Enter recovery code/);
  expect(label).not.toBeNull();
});

test('Verify renders form with correct container class', () => {
  const { container } = render(<Verify {...makeProps()} />);
  const form = container.querySelector('form.mfa-verify-backup-codes__container');
  expect(form).not.toBeNull();
});

test('Verify renders graphic image when graphic and name props are provided', () => {
  const { container } = render(
    <Verify {...makeProps({
      graphic: 'https://example.com/image.png',
      name: 'Test Name'
    })}
    />
  );
  const image = container.querySelector('img.mfa-verify-backup-codes__image');
  expect(image).not.toBeNull();
  expect(image.getAttribute('src')).toBe('https://example.com/image.png');
  expect(image.getAttribute('alt')).toBe('Test Name');
});

test('Verify button submission calls onCompleteVerification with event propagation', () => {
  const onCompleteVerification = jest.fn();
  const { container } = render(
    <Verify {...makeProps({
      onCompleteVerification
    })}
    />
  );
  const input = container.querySelector('input.mfa-verify-backup-codes__input');
  fireEvent.change(input, { target: { value: 'test-code' } });
  const button = container.querySelector('button.btn-primary');
  fireEvent.click(button);
  expect(onCompleteVerification).toHaveBeenCalledWith({ code: 'test-code' });
});

test('Verify input maintains value state across multiple changes', () => {
  const { container } = render(<Verify {...makeProps()} />);
  const input = container.querySelector('input.mfa-verify-backup-codes__input');
  fireEvent.change(input, { target: { value: 'code1' } });
  expect(input.value).toBe('code1');
  fireEvent.change(input, { target: { value: 'code1-updated' } });
  expect(input.value).toBe('code1-updated');
});

test('Verify renders button with correct text', () => {
  render(<Verify {...makeProps()} />);
  const button = screen.getByRole('button', { name: /Next/ });
  expect(button).not.toBeNull();
  expect(button.classList.contains('btn-primary')).toBe(true);
});

test('Verify does not render more options control when not provided', () => {
  const { container } = render(<Verify {...makeProps()} />);
  const items = container.querySelectorAll('.mfa-action-list__item');
  expect(items.length).toBe(1);
});

test('Verify passes correct code value to onCompleteVerification callback', () => {
  const onCompleteVerification = jest.fn();
  const { container } = render(
    <Verify {...makeProps({
      onCompleteVerification
    })}
    />
  );
  const input = container.querySelector('input.mfa-verify-backup-codes__input');
  const testCode = 'ABC123XYZ789';
  fireEvent.change(input, { target: { value: testCode } });
  fireEvent.click(container.querySelector('button.btn-primary'));
  expect(onCompleteVerification).toHaveBeenCalledWith({ code: testCode });
  expect(onCompleteVerification).toHaveBeenCalledTimes(1);
});
