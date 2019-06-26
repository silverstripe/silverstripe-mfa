import Register from 'components/BackupCodes/Register';
import Verify from 'components/BackupCodes/Verify';
import Injector from 'lib/Injector'; // eslint-disable-line

export default () => {
  Injector.component.registerMany({
    BackupCodeRegister: Register,
    BackupCodeVerify: Verify,
  });
};
