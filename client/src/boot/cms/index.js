/* global window */
import registerComponents from './registerComponents';
import registerReducers from './registerReducers';

window.document.addEventListener('DOMContentLoaded', () => {
  registerComponents();
  registerReducers();
});
