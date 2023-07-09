/* global jest, test */

import React from 'react';
import { render, fireEvent } from '@testing-library/react';
import Verify from '../Verify';

window.ss = {
  i18n: { _t: (key, string) => string },
};

test('Verify has a disabled button on load', () => {
  const { container } = render(<Verify />);
  expect(container.querySelector('button.btn-primary').disabled).toBe(true);
});

test('Verify will un-disable the button when input is provided', () => {
  const { container } = render(<Verify />);
  const input = container.querySelector('input.mfa-verify-backup-codes__input');
  fireEvent.change(input, { target: { value: 'x' } });
  expect(container.querySelector('button.btn-primary').disabled).toBe(false);
});

test('Verify renders the given "more options control"', () => {
  const { container } = render(
    <Verify {...{
      moreOptionsControl: <div>More options!</div>
    }}
    />
  );
  expect(container.querySelectorAll('.mfa-action-list__item')[1].textContent).toBe('More options!');
});

test('Verify triggers login completion with the right value when the button is pressed', () => {
  const onCompleteVerification = jest.fn();
  const { container } = render(
    <Verify {...{
      onCompleteVerification
    }}
    />
  );
  const input = container.querySelector('input.mfa-verify-backup-codes__input');
  fireEvent.change(input, { target: { value: 'something' } });
  fireEvent.click(container.querySelector('button.btn-primary'));
  expect(onCompleteVerification).toBeCalledWith({ code: 'something' });
});
