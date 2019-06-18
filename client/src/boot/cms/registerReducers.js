import Injector from 'lib/Injector'; // eslint-disable-line
import baseRegisterReducers from '../registerReducers';
import mfaAdministrationReducer from 'state/mfaAdministration/reducer';

export default () => {
  baseRegisterReducers();

  Injector.reducer.register('mfaAdministration', mfaAdministrationReducer);
};
