/* global window */
import React from 'react';
import { createRoot } from 'react-dom/client';
import registerComponents from 'boot/registerComponents';
import registerReducers from 'boot/registerReducers';
import Injector from 'lib/Injector'; // eslint-disable-line
import { createStore, combineReducers } from 'redux';
import { Provider } from 'react-redux';
import Login from '../containers/Login';

window.document.addEventListener('DOMContentLoaded', () => {
  registerComponents();
  registerReducers();

  Injector.ready(() => {
    const element = window.document.getElementById('mfa-app');
    const schemaURL = element.dataset.schemaurl;

    // todo allow Redux to come from react-injector
    const store = createStore(
      combineReducers(Injector.reducer.getAll()),
      window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__()
    );

    createRoot(element).render(
      <Provider store={store}>
        <Login schemaURL={schemaURL} />
      </Provider>
    );
  });

  // Force this to the end of the execution queue to ensure it's last.
  window.setTimeout(() => Injector.load(), 1);
});
