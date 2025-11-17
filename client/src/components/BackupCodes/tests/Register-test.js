/* global jest, test, expect, afterEach */

import React from 'react';
import { render, fireEvent, screen, waitFor } from '@testing-library/react';
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
    onCompleteRegistration: jest.fn(),
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

test('Register renders description with support link', () => {
  render(<Register {...makeProps()} />);
  const description = screen.getByText(/Recovery codes enable you to log into your account/);
  expect(description).not.toBeNull();
  const supportLink = screen.getByText(/Learn more about recovery codes/);
  expect(supportLink).not.toBeNull();
  expect(supportLink.getAttribute('href')).toBe('https://google.com');
  expect(supportLink.getAttribute('target')).toBe('_blank');
  expect(supportLink.getAttribute('rel')).toBe('noopener noreferrer');
});

test('Register renders description with custom support text', () => {
  render(
    <Register
      {...makeProps({
        method: {
          urlSegment: 'aye',
          name: 'Aye',
          description: 'Register using aye',
          supportLink: 'https://example.com',
          supportText: 'Get help here',
          component: 'Test',
        },
      })}
    />
  );
  const supportLink = screen.getByText('Get help here');
  expect(supportLink).not.toBeNull();
  expect(supportLink.getAttribute('href')).toBe('https://example.com');
});

test('Register renders description without support link when not provided', () => {
  render(
    <Register
      {...makeProps({
        method: {
          urlSegment: 'aye',
          name: 'Aye',
          description: 'Register using aye',
          component: 'Test',
        },
      })}
    />
  );
  const description = screen.getByText(/Recovery codes enable you to log into your account/);
  expect(description).not.toBeNull();
  expect(screen.queryByText(/Learn more about recovery codes/)).toBeNull();
});

test('Register renders print button with correct text', () => {
  const { container } = render(<Register {...makeProps()} />);
  const printButton = screen.getByText('Print codes');
  expect(printButton).not.toBeNull();
  expect(printButton.classList.contains('btn')).toBe(true);
  expect(printButton.classList.contains('btn-link')).toBe(true);
  expect(container.querySelector('.mfa-register-backup-codes__helper-links')).not.toBeNull();
});

test('Register renders download button with correct text', () => {
  render(<Register {...makeProps()} />);
  const downloadLink = screen.getByText('Download');
  expect(downloadLink).not.toBeNull();
  expect(downloadLink.classList.contains('btn')).toBe(true);
  expect(downloadLink.classList.contains('btn-link')).toBe(true);
});

test('Register renders download button with correct filename', () => {
  render(
    <Register
      {...makeProps({
        method: {
          urlSegment: 'aye',
          name: 'Recovery Codes',
          description: 'Register using aye',
          supportLink: 'https://google.com',
          component: 'Test',
        },
      })}
    />
  );
  const downloadLink = screen.getByText('Download');
  expect(downloadLink.getAttribute('download')).toBe('Recovery Codes.txt');
});

test('Register renders codes in a code grid', () => {
  const { container } = render(
    <Register {...makeProps({ codes: ['111111111111', '222222222222', '333333333333'] })} />
  );
  const codeGrid = container.querySelector('.mfa-register-backup-codes__code-grid');
  expect(codeGrid).not.toBeNull();
  const codeElements = codeGrid.querySelectorAll('div');
  expect(codeElements.length).toBe(3);
});

test('Register formats codes correctly in grid', () => {
  const { container } = render(
    <Register {...makeProps({ codes: ['123456789012'] })} />
  );
  const codeGrid = container.querySelector('.mfa-register-backup-codes__code-grid');
  expect(codeGrid.textContent).toContain('1234 5678 9012');
});

test('Register print button is clickable', () => {
  render(<Register {...makeProps()} />);
  const printButton = screen.getByText('Print codes');
  expect(printButton).not.toBeNull();
  expect(typeof printButton.onclick).not.toBe('undefined');
});

test('Register copy button joins codes with newlines', () => {
  const { container } = render(
    <Register {...makeProps({ codes: ['code1', 'code2', 'code3'] })} />
  );
  const copyButton = container.querySelector('.mfa-register-backup-codes__copy-to-clipboard');
  expect(copyButton).not.toBeNull();
  fireEvent.click(copyButton);
  expect(copyButton).not.toBeNull();
});

test('Register renders finish button with correct text and class', () => {
  render(<Register {...makeProps()} />);
  const finishButton = screen.getByText('Finish');
  expect(finishButton).not.toBeNull();
  expect(finishButton.classList.contains('btn')).toBe(true);
  expect(finishButton.classList.contains('btn-primary')).toBe(true);
});

test('Register renders with correct container class', () => {
  const { container } = render(<Register {...makeProps()} />);
  const mainContainer = container.querySelector('.mfa-register-backup-codes__container');
  expect(mainContainer).not.toBeNull();
});

test('Register uses default copyFeedbackDuration when not provided', async () => {
  const { container } = render(
    <Register
      {...makeProps({
        copyFeedbackDuration: undefined,
        codes: ['123', '456'],
      })}
    />
  );
  const copyButton = container.querySelector('.mfa-register-backup-codes__copy-to-clipboard');
  expect(copyButton.textContent).toBe('Copy codes');
  fireEvent.click(copyButton);
  await waitFor(() => {
    expect(copyButton.textContent).toBe('Copied!');
  });
  await waitFor(() => {
    expect(copyButton.textContent).toBe('Copy codes');
  }, { timeout: 4000 });
});
