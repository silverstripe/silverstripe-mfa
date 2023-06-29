/* global jest, test */

import React from 'react';
import { render, fireEvent, screen } from '@testing-library/react';
import Register from '../Register';

window.ss = {
  i18n: { _t: (key, string) => string },
};

window.prompt = () => {};

function makeProps(obj = {}) {
  return {
    method: {
      urlSegment: 'aye',
      name: 'Aye',
      description: 'Register using aye',
      supportLink: 'https://google.com',
      component: 'Test',
    },
    codes: ['123', '456'],
    copyFeedbackDuration: 30,
    ...obj
  };
}

test('Register will show a recently copied message when using the copy test button and hide after a short delay', async () => {
  const { container } = render(<Register {...makeProps()}/>);
  let link = container.querySelector('.mfa-register-backup-codes__copy-to-clipboard');
  expect(link.textContent).toBe('Copy codes');
  fireEvent.click(link);
  link = await screen.findByText('Copied!');
  expect(link.classList).toContain('mfa-register-backup-codes__copy-to-clipboard');
  expect(screen.queryByText('Copy codes!')).toBeNull();
  link = await screen.findByText('Copy codes');
  expect(link.classList).toContain('mfa-register-backup-codes__copy-to-clipboard');
  expect(screen.queryByText('Copied!')).toBeNull();
  // Can do this multiple times
  fireEvent.click(link);
  link = await screen.findByText('Copied!');
  expect(link).not.toBeNull();
  link = await screen.findByText('Copy codes');
  expect(link).not.toBeNull();
});

test('Register will call the given onComplete function when pressing the "finish" button', () => {
  const onCompleteRegistration = jest.fn();
  const { container } = render(
    <Register {...makeProps({
      onCompleteRegistration
    })}
    />
  );
  fireEvent.click(container.querySelector('button.btn-primary'));
  expect(onCompleteRegistration).toHaveBeenCalled();
});
