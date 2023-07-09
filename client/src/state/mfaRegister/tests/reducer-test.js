/* global jest, describe, it, expect */

import {
  SCREEN_INTRODUCTION,
  SCREEN_CHOOSE_METHOD,
} from 'components/Register';
import reducer from '../reducer';
import MFA_REGISTER from '../actionTypes';

describe('MFARegister reducer', () => {
  it('should return the initial state', () => {
    expect(reducer(undefined, {})).toEqual({
      availableMethods: [],
      method: null,
      screen: SCREEN_INTRODUCTION,
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

  it('should handle SET_AVAILABLE_METHODS', () => {
    expect(reducer({}, {
      type: MFA_REGISTER.SET_AVAILABLE_METHODS,
      payload: { availableMethods: ['test'] }
    })).toEqual({
      availableMethods: ['test'],
    });
  });

  it('should handle ADD_AVAILABLE_METHOD', () => {
    expect(reducer({
      availableMethods: ['one'],
    }, {
      type: MFA_REGISTER.ADD_AVAILABLE_METHOD,
      payload: { method: 'two' }
    })).toEqual({
      availableMethods: ['one', 'two'],
    });
  });

  it('should handle REMOVE_AVAILABLE_METHOD', () => {
    expect(reducer({
      availableMethods: [
        { urlSegment: 'one' },
        { urlSegment: 'two' }
      ],
    }, {
      type: MFA_REGISTER.REMOVE_AVAILABLE_METHOD,
      payload: { method: { urlSegment: 'one' } }
    })).toEqual({
      availableMethods: [{ urlSegment: 'two' }],
    });
  });
});
