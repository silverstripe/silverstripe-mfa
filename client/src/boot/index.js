/* global window */
import React from 'react';
import ReactDOM from 'react-dom';
import Login from '../containers/Login';
import registerComponents from 'boot/registerComponents';
import Injector from 'lib/Injector'; // eslint-disable-line

window.document.addEventListener('DOMContentLoaded', () => {
  registerComponents();

  const element = window.document.getElementById('mfa-app');
  const schemaURL = element.dataset.schemaurl;
  ReactDOM.render(<Login schemaURL={schemaURL} />, element);

  // Force this to the end of the execution queue to ensure it's last.
  window.setTimeout(() => Injector.load(), 1);
});

