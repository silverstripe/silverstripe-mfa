/* global jest, describe, it, expect */

import reducer from '../reducer';
import MFA_VERIFY from '../actionTypes';

describe('MFAVerify reducer', () => {
  it('should return the initial state', () => {
    expect(reducer(undefined, {})).toEqual({
      allMethods: [],
    });
  });

  it('should handle SET_ALL_METHODS', () => {
    expect(reducer({}, {
      type: MFA_VERIFY.SET_ALL_METHODS,
      payload: { allMethods: ['foo'] }
    })).toEqual({
      allMethods: ['foo'],
    });
  });
});
