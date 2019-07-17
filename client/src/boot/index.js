/* global window */
import React from 'react';
import ReactDOM from 'react-dom';
import Login from '../containers/Login';
import registerComponents from 'boot/registerComponents';
import registerReducers from 'boot/registerReducers';
import Injector from 'lib/Injector'; // eslint-disable-line
import { createStore, combineReducers } from 'redux';
import { Provider } from 'react-redux';

window.document.addEventListener('DOMContentLoaded', () => {
  registerComponents();
  registerReducers();

  Injector.ready(() => {
    const element = window.document.getElementById('mfa-app');
    const schemaURL = element.dataset.schemaurl;

    const store = createStore(
      combineReducers(Injector.reducer.getAll()),
      window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__()
    );

    ReactDOM.render(
      <Provider store={store}>
        <Login schemaURL={schemaURL} />
      </Provider>,
      element
    );
  });

  // Force this to the end of the execution queue to ensure it's last.
  window.setTimeout(() => Injector.load(), 1);
});

