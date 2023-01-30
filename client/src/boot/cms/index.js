/* global window */
import registerComponents from './registerComponents';
import registerReducers from './registerReducers';
import registerTransformations from './registerTransformations';

window.document.addEventListener('DOMContentLoaded', () => {
  registerComponents();
  registerReducers();
  registerTransformations();
});
