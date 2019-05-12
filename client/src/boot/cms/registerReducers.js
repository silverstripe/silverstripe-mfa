import Injector from 'lib/Injector'; // eslint-disable-line
import mfaRegisterReducer from 'state/mfaRegister/reducer';

export default () => {
  Injector.reducer.register('mfaRegister', mfaRegisterReducer);
};

