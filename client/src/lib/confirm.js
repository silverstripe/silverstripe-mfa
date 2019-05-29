import React from 'react';
import ReactDOM from 'react-dom';
import Confirmation from 'components/Confirmation';

const confirmation = (
  message,
  additionalProps = {},
  mountNode = document.body,
  unmountDelay = 350,
  Component,
) => {
  const ConfirmComponent = Component || Confirmation;
  const wrapper = mountNode.appendChild(document.createElement('div'));

  return new Promise(resolve => {
    const complete = result => () => {
      resolve(result);
      setTimeout(() => {
        ReactDOM.unmountComponentAtNode(wrapper);
        setTimeout(() => mountNode.removeChild(wrapper));
      }, unmountDelay);
    };

    ReactDOM.render(
      <ConfirmComponent
        {...additionalProps}
        onConfirm={complete(true)}
        onCancel={complete(false)}
        body={message}
      />,
      wrapper
    );
  });
};

export default confirmation;
