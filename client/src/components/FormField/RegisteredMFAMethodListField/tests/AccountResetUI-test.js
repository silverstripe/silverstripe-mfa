/* global jest, describe, it, expect */

// eslint-disable-next-line no-unused-vars
import fetch from 'isomorphic-fetch';
import React from 'react';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import AccountResetUI from '../AccountResetUI';

import confirm from 'reactstrap-confirm';

jest.mock('reactstrap-confirm', () => jest.fn().mockImplementation(
  () => Promise.resolve(true)
));

Enzyme.configure({ adapter: new Adapter() });

window.ss = {
  i18n: { _t: (key, string) => string },
};

const fetchMock = jest.spyOn(global, 'fetch');

describe('AccountResetUI', () => {
  beforeEach(() => {
    fetchMock.mockImplementation(() => Promise.resolve({
      status: 200,
      json: () => Promise.resolve({ success: true }),
    }));

    fetchMock.mockClear();
    confirm.mockClear();
  });

  describe('renderAction()', () => {
    it('is disabled when an endpoint has not been supplied', () => {
      const ui = shallow(
        <AccountResetUI />
      );

      const action = ui.find('.account-reset-action .btn');

      expect(action).toHaveLength(1);
      expect(action.first().props().disabled).toBeTruthy();
    });

    it('is enabled when an endpoint has been supplied', () => {
      const ui = shallow(
        <AccountResetUI resetEndpoint="/reset/1" />
      );

      const action = ui.find('.account-reset-action .btn');

      expect(action).toHaveLength(1);
      expect(action.first().props().disabled).toBeFalsy();
    });

    it('submits the reset request when clicked', done => {
      const ui = shallow(
        <AccountResetUI resetEndpoint="/reset/1" />
      );

      ui.find('.account-reset-action .btn').first().simulate('click');

      setTimeout(() => {
        expect(fetchMock.mock.calls.length).toBe(1);
        done();
      });
    });

    it('is hidden when submitting or complete', () => {
      const ui = shallow(
        <AccountResetUI />
      );

      ui.instance().setState({
        submitting: true,
      });

      let action = ui.find('.account-reset-action .btn');

      expect(action).toHaveLength(0);

      ui.instance().setState({
        submitting: false,
        complete: true,
      });

      action = ui.find('.account-reset-action .btn');

      expect(action).toHaveLength(0);
    });
  });

  describe('renderStatusMessage()', () => {
    it('does not display a status by default', () => {
      const ui = shallow(
        <AccountResetUI />
      );

      expect(ui.find('.account-reset-action__message')).toHaveLength(0);
    });

    it('displays a sending status when the form is submitted', () => {
      const ui = shallow(
        <AccountResetUI />
      );

      ui.instance().setState({
        submitting: true,
      });

      const message = ui.find('.account-reset-action__message');

      expect(message).toHaveLength(1);
      expect(message.text()).toContain('Sending...');
    });

    it('displays an error status when the request fails', () => {
      const ui = shallow(
        <AccountResetUI />
      );

      ui.instance().setState({
        complete: true,
        failed: true,
      });

      const message = ui.find('.account-reset-action__message');

      expect(message).toHaveLength(1);
      expect(message.text()).toContain('unable to send an email');
    });

    it('displays a complete status when the request succeeds', () => {
      const ui = shallow(
        <AccountResetUI />
      );

      ui.instance().setState({
        complete: true,
        failed: false,
      });

      const message = ui.find('.account-reset-action__message');

      expect(message).toHaveLength(1);
      expect(message.text()).toContain('email has been sent');
    });
  });
});
