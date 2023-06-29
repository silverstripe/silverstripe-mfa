/* global jest, test, describe, it, expect */

import React from 'react';
import { render, screen, fireEvent, act } from '@testing-library/react';
import {
  Component as Register,
  SCREEN_REGISTER_METHOD,
  SCREEN_CHOOSE_METHOD,
  SCREEN_COMPLETE
} from '../Register';

let lastApiCallArgs;
let resolveApiCall;
// eslint-disable-next-line no-unused-vars
let rejectApiCall;

jest.mock('lib/api', () => ({
  __esModule: true,
  default: (endpoint, method, body, headers) => {
    lastApiCallArgs = { endpoint, method, body, headers };
    return new Promise((resolve, reject) => {
      resolveApiCall = resolve;
      rejectApiCall = reject;
    });
  }
}));

jest.mock('lib/Injector', () => ({
  __esModule: true,
  loadComponent: (component) => {
    if (component === 'TestRegistration') {
      return ({ method, onBack, onCompleteRegistration }) => (
        <div data-testid="test-registration" data-method={method.urlSegment} onClick={() => onCompleteRegistration({ my: 'regodata' })}>
          <input data-testid="test-back" onClick={onBack}/>
        </div>
      );
    }
    return null;
  }
}));

window.ss = {
  i18n: { _t: (key, string) => string },
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
    endpoints: {
      register: '/fake/{urlSegment}',
      skip: '/fake/skip',
      complete: '/fake/complete',
    },
    screen: SCREEN_REGISTER_METHOD,
    availableMethods: [firstMethod, secondMethod],
    registeredMethods: [],
    selectedMethod: firstMethod,
    onSelectMethod: () => null,
    backupMethod: null,
    canSkip: true,
    onCompleteRegistration: () => null,
    onRemoveAvailableMethod: () => null,
    IntroductionComponent: ({ canSkip }) => (
      <div data-testid="test-introduction">
        {canSkip && <div data-testid="test-skip"/>}
      </div>
    ),
    TitleComponent: () => <div className="test-title"/>,
    SelectMethodComponent: ({ methods }) => (
      <div className="test-select-method">
        {methods.map((method) => <div key={method.name} data-testid="test-method">{method.description}</div>)}
      </div>
    ),
    CompleteComponent: ({ onComplete }) => (
      <div data-testid="test-complete">
        <input data-testid="test-complete-button" onClick={onComplete}/>
      </div>
    ),
    ...obj,
  };
}

test('Register sets the selected method as the backup method', async () => {
  let doResolve;
  const promise = new Promise((resolve) => {
    doResolve = resolve;
  });
  const onSelectMethod = jest.fn(() => doResolve());
  render(
    <Register {...makeProps({
      backupMethod: {
        name: 'Test'
      },
      onSelectMethod
    })}
    />
  );
  // api call is triggered as part of componentDidMount()
  resolveApiCall({
    json: () => Promise.resolve({
      SecurityID: 'foo',
      registerProps: {
        bar: 'baz'
      }
    })
  });
  const registration = await screen.findByTestId('test-registration');
  // it this test the onClick handler triggers the onCompleteRegistration handler
  fireEvent.click(registration);
  resolveApiCall({
    status: 201,
  });
  // Prevent the "Warning: An update to Register inside a test was not wrapped in act(...)" warning
  /* eslint-disable-next-line no-return-await */
  await act(async () => await promise);
  expect(onSelectMethod).toHaveBeenCalledWith({ name: 'Test' });
});

test('Register clears the selected method and sets to be completed', async () => {
  let doResolve;
  const promise = new Promise((resolve) => {
    doResolve = resolve;
  });
  const onSelectMethod = jest.fn();
  const onShowComplete = jest.fn(() => doResolve());
  render(
    <Register {...makeProps({
      onSelectMethod,
      onShowComplete
    })}
    />
  );
  resolveApiCall({
    json: () => Promise.resolve({
      SecurityID: 'foo',
      registerProps: {
        bar: 'baz'
      }
    })
  });
  const registration = await screen.findByTestId('test-registration');
  fireEvent.click(registration);
  resolveApiCall({
    status: 201,
  });
  /* eslint-disable-next-line no-return-await */
  await act(async () => await promise);
  expect(onSelectMethod).not.toHaveBeenCalled();
  expect(onShowComplete).toHaveBeenCalled();
});

test('Register calls the API with the correct params', async () => {
  let doResolve;
  const promise = new Promise((resolve) => {
    doResolve = resolve;
  });
  const onShowComplete = jest.fn(() => doResolve());
  render(
    <Register {...makeProps({
      onShowComplete
    })}
    />
  );
  resolveApiCall({
    json: () => Promise.resolve({
      SecurityID: 'foo',
      registerProps: {
        bar: 'baz'
      }
    })
  });
  expect(lastApiCallArgs.endpoint).toBe('/fake/aye');
  expect(lastApiCallArgs.method).toBe(undefined);
  expect(lastApiCallArgs.body).toBe(undefined);
  const registration = await screen.findByTestId('test-registration');
  fireEvent.click(registration);
  resolveApiCall({
    status: 201,
  });
  /* eslint-disable-next-line no-return-await */
  await act(async () => await promise);
  expect(onShowComplete).toHaveBeenCalled();
  expect(lastApiCallArgs.endpoint).toBe('/fake/aye?SecurityID=foo');
  expect(lastApiCallArgs.method).toBe('POST');
  expect(lastApiCallArgs.body).toBe('{"my":"regodata"}');
});

test('Register calls the onShowChooseMethod callback on back', async () => {
  let doResolve;
  const promise = new Promise((resolve) => {
    doResolve = resolve;
  });
  const onShowChooseMethod = jest.fn(() => doResolve());
  render(
    <Register {...makeProps({
      onShowChooseMethod,
    })}
    />
  );
  resolveApiCall({
    json: () => Promise.resolve({})
  });
  const backButton = await screen.findByTestId('test-back');
  fireEvent.click(backButton);
  /* eslint-disable-next-line no-return-await */
  await act(async () => await promise);
  expect(onShowChooseMethod).toHaveBeenCalled();
});

test('Register will load the component for the chosen method', async () => {
  render(
    <Register {...makeProps()}/>
  );
  resolveApiCall({
    json: () => Promise.resolve({})
  });
  const registration = await screen.findByTestId('test-registration');
  expect(registration.getAttribute('data-method')).toBe('aye');
});

test('Register renders a SelectMethod with available methods to register passed', async () => {
  render(
    <Register {...makeProps({
      screen: SCREEN_CHOOSE_METHOD
    })}
    />
  );
  resolveApiCall({
    json: () => Promise.resolve({})
  });
  await screen.findByText('Register using aye');
  const methods = screen.getAllByTestId('test-method');
  expect(methods[0].textContent).toBe('Register using aye');
  expect(methods[1].textContent).toBe('Register using bee');
});

test('Register renders the Introduction UI on first load', async () => {
  render(
    <Register {...makeProps({
      screen: null
    })}
    />
  );
  const intro = await screen.findByTestId('test-introduction');
  expect(intro).not.toBeNull();
});

test('Register indicates introduction can skip if the skip endpoint is defined', async () => {
  render(
    <Register {...makeProps({
      screen: null,
    })}
    />
  );
  await screen.findByTestId('test-introduction');
  expect(screen.queryByTestId('test-skip')).not.toBeNull();
});

test('Register indicates introduction cannot skip if the skip endpoint is not defined', async () => {
  render(
    <Register {...makeProps({
      screen: null,
      endpoints: {
        ...makeProps().endpoints,
        skip: null
      }
    })}
    />
  );
  await screen.findByTestId('test-introduction');
  expect(screen.queryByTestId('test-skip')).toBeNull();
});

test('Register renders the complete screen', async () => {
  render(
    <Register {...makeProps({
      screen: SCREEN_COMPLETE,
    })}
    />
  );
  const complete = await screen.findByTestId('test-complete');
  expect(complete).not.toBeNull();
});

test('Register renders a button in complete that triggers onCompleteRegistration', async () => {
  const onCompleteRegistration = jest.fn();
  render(
    <Register {...makeProps({
      screen: SCREEN_COMPLETE,
      onCompleteRegistration
    })}
    />
  );
  await screen.findByTestId('test-complete');
  const completeButton = screen.getByTestId('test-complete-button');
  fireEvent.click(completeButton);
  expect(onCompleteRegistration).toHaveBeenCalled();
});
