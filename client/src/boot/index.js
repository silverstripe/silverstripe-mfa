/* global window */
import React from 'react';
import ReactDOM from 'react-dom';
import MultiFactorApp from '../containers/MultiFactorApp';

window.document.addEventListener('DOMContentLoaded', () => {
  // registerComponents();
  const element = window.document.getElementById('mfa-app');
  const schemaURL = element.dataset.schemaurl;
  ReactDOM.render(<MultiFactorApp id={element.id} schemaURL={schemaURL} />, element);
});
