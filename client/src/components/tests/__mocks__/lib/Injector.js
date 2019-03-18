/* global jest */

import React from 'react';

export const loadComponent = jest.fn().mockImplementation(name => {
  // Create a fake HOC to return
  const hoc = () => <div />;
  // Set the display name to the name of the given component
  hoc.displayName = name;
  return hoc;
});
