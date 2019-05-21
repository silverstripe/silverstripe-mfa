/* global jest */

const Config = {
  get: jest.fn().mockImplementation(key => key),
};

export default Config;
