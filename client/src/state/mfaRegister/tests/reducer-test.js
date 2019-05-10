/* global jest, describe, it, expect */

import reducer from '../reducer';
import MFA_REGISTER from '../actionTypes';
import {
  SCREEN_INTRODUCTION,
  SCREEN_CHOOSE_METHOD,
} from 'components/Register';

describe('MFARegister reducer', () => {
  it('should return the initial state', () => {
    expect(reducer(undefined, {})).toEqual({
      screen: SCREEN_INTRODUCTION,
      method: null,
    });
  });

  it('should handle SET_SCREEN', () => {
    expect(reducer({}, {
      type: MFA_REGISTER.SET_SCREEN,
      payload: { screen: SCREEN_CHOOSE_METHOD }
    })).toEqual({
      screen: SCREEN_CHOOSE_METHOD,
    });
  });

  it('should handle SET_METHOD', () => {
    expect(reducer({}, {
      type: MFA_REGISTER.SET_METHOD,
      payload: { method: 'test' }
    })).toEqual({
      method: 'test',
    });
  });
});
