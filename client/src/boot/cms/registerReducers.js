import Injector from 'lib/Injector'; // eslint-disable-line
import mfaAdministrationReducer from 'state/mfaAdministration/reducer';
import baseRegisterReducers from '../registerReducers';

export default () => {
  baseRegisterReducers();

  Injector.reducer.register('mfaAdministration', mfaAdministrationReducer);
};
