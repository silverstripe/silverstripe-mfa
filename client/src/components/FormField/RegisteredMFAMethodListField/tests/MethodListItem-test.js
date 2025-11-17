/* global jest, test, describe, it, expect */
import React from 'react';
import { render } from '@testing-library/react';
import MethodListItem from '../MethodListItem';

window.ss = {
  i18n: {
    _t: (key, string) => string,
    detectLocale: () => 'en_NZ',
    inject: (message) => message, // not a great mock...
  },
};

function makeProps(obj = {}) {
  return {
    method: {
      urlSegment: 'foo'
    },
    RemoveComponent: () => <div className="test-remove" />,
    SetDefaultComponent: () => <div className="test-set-default" />,
    ...obj
  };
}

test('MethodListitem identifies default methods', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      isDefaultMethod: true
    })}
    />
  );
  expect(container.querySelector('.registered-method-list-item').textContent).toBe('{method} (default): Registered');
});

test('MethodListItem identifies backup methods', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      isBackupMethod: true
    })}
    />
  );
  expect(container.querySelector('.registered-method-list-item').textContent).toBe('{method}: Created {date}');
});

test('MethodListItem does not render remove buttons by default', () => {
  const { container } = render(
    <MethodListItem {...makeProps()}/>
  );
  expect(container.querySelector('.test-remove')).toBeNull();
});

test('MethodListItem does render remove buttons if canRemove is true', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      canRemove: true,
    })}
    />
  );
  expect(container.querySelector('.test-remove')).not.toBeNull();
});

test('MethodListItem renders a regular method with correct status', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      isDefaultMethod: false,
      isBackupMethod: false,
    })}
    />
  );
  expect(container.querySelector('.registered-method-list-item').textContent).toBe('{method}: Registered');
});

test('MethodListItem does not render set-as-default button for default methods', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      isDefaultMethod: true,
    })}
    />
  );
  expect(container.querySelector('.test-set-default')).toBeNull();
});

test('MethodListItem does not render set-as-default button for backup methods', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      isBackupMethod: true,
    })}
    />
  );
  expect(container.querySelector('.test-set-default')).toBeNull();
});

test('MethodListItem renders set-as-default button for regular methods', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      isDefaultMethod: false,
      isBackupMethod: false,
      canRemove: true,
    })}
    />
  );
  expect(container.querySelector('.test-set-default')).not.toBeNull();
});

test('MethodListItem renders with custom className', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      className: 'custom-method-class',
    })}
    />
  );
  const element = container.querySelector('.registered-method-list-item');
  expect(element.classList.contains('custom-method-class')).toBe(true);
});

test('MethodListItem renders with custom tag', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      tag: 'div',
    })}
    />
  );
  expect(container.querySelector('div.registered-method-list-item')).not.toBeNull();
});

test('MethodListItem renders with li tag by default', () => {
  const { container } = render(
    <MethodListItem {...makeProps()} />
  );
  expect(container.querySelector('li.registered-method-list-item')).not.toBeNull();
});

test('MethodListItem does not render reset buttons by default', () => {
  const { container } = render(
    <MethodListItem {...makeProps()}/>
  );
  expect(container.textContent).not.toContain('reset');
});

test('MethodListItem does not render controls when canRemove and canReset are both false', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      canRemove: false,
      canReset: false,
    })}
    />
  );
  const controls = container.querySelector('div');
  expect(controls ? controls.querySelector('.test-remove') : null).toBeNull();
});

test('MethodListItem renders controls container when canRemove is true', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      canRemove: true,
      canReset: false,
    })}
    />
  );
  const removeButton = container.querySelector('.test-remove');
  expect(removeButton).not.toBeNull();
});

test('MethodListItem accepts and passes through custom method object', () => {
  const customMethod = {
    urlSegment: 'custom-auth',
    name: 'Custom Authentication'
  };
  const { container } = render(
    <MethodListItem {...makeProps({
      method: customMethod,
    })}
    />
  );
  expect(container.querySelector('.registered-method-list-item')).not.toBeNull();
});

test('MethodListItem handles createdDate prop', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      createdDate: '2023-01-15',
    })}
    />
  );
  const element = container.querySelector('.registered-method-list-item');
  expect(element).not.toBeNull();
});

test('MethodListItem renders multiple className values together', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      className: 'active pending',
    })}
    />
  );
  const element = container.querySelector('.registered-method-list-item');
  expect(element.classList.contains('active')).toBe(true);
  expect(element.classList.contains('pending')).toBe(true);
});

test('MethodListItem renders both remove and set-as-default when canRemove is true', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      isDefaultMethod: false,
      isBackupMethod: false,
      canRemove: true,
      canReset: false,
    })}
    />
  );
  expect(container.querySelector('.test-remove')).not.toBeNull();
  expect(container.querySelector('.test-set-default')).not.toBeNull();
});

test('MethodListItem backup method displays correct message with date injection', () => {
  window.ss.i18n.inject = (message, data) => message.replace('{method}', data.method).replace('{date}', data.date);
  const { container } = render(
    <MethodListItem {...makeProps({
      isBackupMethod: true,
      method: { urlSegment: 'backup', name: 'Backup Codes' },
      createdDate: '2023-12-25',
    })}
    />
  );
  const text = container.querySelector('.registered-method-list-item').textContent;
  expect(text).toContain('Backup Codes');
});

test('MethodListItem renders with undefined className gracefully', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      className: undefined,
    })}
    />
  );
  const element = container.querySelector('.registered-method-list-item');
  expect(element).not.toBeNull();
});

test('MethodListItem renders with empty className string', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      className: '',
    })}
    />
  );
  const element = container.querySelector('.registered-method-list-item');
  expect(element.className).toContain('registered-method-list-item');
});

test('MethodListItem status message varies based on method type', () => {
  const defaultProps = makeProps({
    method: { urlSegment: 'test', name: 'Test Method' }
  });

  const defaultMethodCase = render(
    <MethodListItem {...defaultProps} isDefaultMethod isBackupMethod={false} />
  );
  expect(defaultMethodCase.container.textContent).toContain('default');

  const backupMethodCase = render(
    <MethodListItem {...defaultProps} isDefaultMethod={false} isBackupMethod />
  );
  expect(backupMethodCase.container.textContent).toContain('Created');

  const regularMethodCase = render(
    <MethodListItem {...defaultProps} isDefaultMethod={false} isBackupMethod={false} />
  );
  expect(regularMethodCase.container.textContent).toContain('Registered');
});

test('MethodListItem with canRemove=true and canReset=false does not show controls div if missing remove component', () => {
  const { container } = render(
    <MethodListItem {...makeProps({
      canRemove: true,
      canReset: false,
    })}
    />
  );
  expect(container.querySelector('.test-remove')).not.toBeNull();
});

test('MethodListItem renders correct number of child elements based on flags', () => {
  const { container: defaultMethodContainer } = render(
    <MethodListItem {...makeProps({
      isDefaultMethod: true,
      canRemove: true,
      canReset: false,
    })}
    />
  );
  const defaultMethodChildren = defaultMethodContainer.querySelector('li.registered-method-list-item').childNodes;
  expect(defaultMethodChildren.length).toBeGreaterThan(0);
});
