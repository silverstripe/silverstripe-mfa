/* global jest, describe, it, expect */

import reducer from '../reducer';
import MFA_ADMINISTRATION from '../actionTypes';

const fakeMethod = {
  urlSegment: 'one',
};

describe('MFAAdministration reducer', () => {
  it('should return the initial state', () => {
    expect(reducer(undefined, {})).toEqual({
      registeredMethods: [],
    });
  });

  it('should handle ADD_REGISTERED_METHOD', () => {
    expect(reducer({}, {
      type: MFA_ADMINISTRATION.ADD_REGISTERED_METHOD,
      payload: { method: fakeMethod }
    })).toEqual({
      registeredMethods: [fakeMethod],
    });
  });

  it('should handle REMOVE_REGISTERED_METHOD', () => {
    expect(reducer({
      registeredMethods: [
        { urlSegment: 'test' },
        fakeMethod,
        { urlSegement: 'dummy' },
      ]
    }, {
      type: MFA_ADMINISTRATION.REMOVE_REGISTERED_METHOD,
      payload: { method: fakeMethod }
    })).toEqual({
      registeredMethods: [
        { urlSegment: 'test' },
        { urlSegement: 'dummy' },
      ],
    });
  });

  it('should handle SET_REGISTERED_METHODS', () => {
    const methods = [
      { urlSegment: 'test' },
      fakeMethod,
      { urlSegement: 'dummy' },
    ];

    expect(reducer({}, {
      type: MFA_ADMINISTRATION.SET_REGISTERED_METHODS,
      payload: { methods }
    })).toEqual({
      registeredMethods: methods,
    });
  });
});
