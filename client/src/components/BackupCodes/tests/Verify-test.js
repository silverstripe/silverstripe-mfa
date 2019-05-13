/* global jest */

import React from 'react';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import Verify from '../Verify';

Enzyme.configure({ adapter: new Adapter() });

window.ss = {
  i18n: { _t: (key, string) => string },
};

describe('Login - Recovery Codes', () => {
  it('has a disabled button on load', () => {
    const wrapper = shallow(<Verify />);

    expect(wrapper.find('button').prop('disabled')).toBe(true);
  });

  it('will un-disable the button when input is provided', () => {
    const wrapper = shallow(<Verify />);

    wrapper.setState({
      value: 'something',
    });
    wrapper.update();

    expect(wrapper.find('button').prop('disabled')).toBe(false);
  });

  it('renders the given "more options control"', () => {
    const moreOptions = <div>More options!</div>;

    const wrapper = shallow(<Verify moreOptionsControl={moreOptions} />);

    expect(wrapper.html()).toMatch(/<div>More options!<\/div>/);
  });

  it('triggers login completion with the right value when the button is pressed', () => {
    const completeFunction = jest.fn();
    const preventDefault = jest.fn();

    const wrapper = shallow(<Verify onCompleteVerification={completeFunction} />);

    wrapper.setState({
      value: 'something',
    });

    wrapper.find('button').simulate('click', { preventDefault });

    expect(preventDefault.mock.calls).toHaveLength(1);
    expect(completeFunction.mock.calls).toHaveLength(1);
    expect(completeFunction.mock.calls[0]).toEqual([{ code: 'something' }]);
  });
});
