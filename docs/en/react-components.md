# Front-end React components

This module provides two main React components that deal with the two facets of multi factor authentication: 
[Register](../../client/src/components/Register.js) and [Verify](../../client/src/components/Verify.js), along with a 
few components that handle configuration in the CMS.

## Configuring requirements

<!-- TODO: Information about Requirements and Injector -->

## Using `Register` and `Login`

### Using the components

Refer to the prop type definitions and their documentation in the source code for each component, listed above.

### State management

The `Register` component manages its state with Redux. If this component is used within admin screens then the Redux 
reducer will already be registered with the store which will be automatically provided with the `Register` component so 
long as `Injector` is used. Actions available for the Redux store are defined and documented in 
[the source code](../../client/src/state/mfaRegister/actions.js).

<!-- TODO: Documentation for consuming Injector outside of admin context? -->
