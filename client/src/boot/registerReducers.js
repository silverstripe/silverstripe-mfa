import Injector from 'lib/Injector'; // eslint-disable-line
import mfaRegisterReducer from 'state/mfaRegister/reducer';
import mfaVerifyReducer from 'state/mfaVerify/reducer';

export default () => {
  Injector.reducer.register('mfaRegister', mfaRegisterReducer);
  Injector.reducer.register('mfaVerify', mfaVerifyReducer);
};
