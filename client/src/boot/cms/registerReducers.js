import Injector from 'lib/Injector'; // eslint-disable-line
import mfaRegisterReducer from 'state/mfaRegister/reducer';
import mfaAdministrationReducer from 'state/mfaAdministration/reducer';

export default () => {
  Injector.reducer.register('mfaRegister', mfaRegisterReducer);
  Injector.reducer.register('mfaAdministration', mfaAdministrationReducer);
};

