/* global jest */

import React from 'react';

const generateHOC = name => {
  // Create a fake HOC to return
  const hoc = () => <div />;
  // Set the display name to the name of the given component
  hoc.displayName = name;
  return hoc;
};

export const loadComponent = jest.fn().mockImplementation(generateHOC);

export const inject = (definitions, callback) => InjectedComponent => props => (
  <InjectedComponent
    {...props}
    {...callback(...definitions.map(name => generateHOC(name)))}
  />
);
