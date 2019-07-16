/* global window */
import registerComponents from './registerComponents';
import registerReducers from './registerReducers';
import { combineReducers, createStore } from 'redux';
import Injector from 'lib/Injector'; // eslint-disable-line

window.document.addEventListener('DOMContentLoaded', () => {
  registerComponents();
  registerReducers();

  // Tell Injector to tag the dom when it's ready (see admins' `boot/index`)
  Injector.ready(() => {
    if (window.jQuery) {
      // need to separate class adds ...because entwine...
      window.jQuery('body')
        .addClass('js-react-boot')
        .addClass('js-injector-boot');
    }

    window.ss.store = createStore(
      combineReducers(Injector.reducer.getAll()),
      window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__()
    );
  });

  // Force this to the end of the execution queue to ensure it's last.
  window.setTimeout(() => Injector.load(), 0);
});
