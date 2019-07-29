/* global jest, describe, it */

// eslint-disable-next-line no-unused-vars
import fetch from 'isomorphic-fetch';

import api from '../api';

const fetchMock = jest.spyOn(global, 'fetch');

describe('api()', () => {
  beforeEach(() => {
    fetchMock.mockImplementation(() => Promise.resolve({
      status: 200,
      json: () => Promise.resolve({}),
    }));
    fetchMock.mockClear();
  });

  it('generates a fetch request and returns it', () => {
    const output = api('/');

    expect(fetchMock).toHaveBeenCalledWith(
      '/',
      {
        body: undefined,
        credentials: 'same-origin',
        headers: {},
        method: 'GET',
      }
    );

    expect(output).toBeInstanceOf(Promise);
  });
});
