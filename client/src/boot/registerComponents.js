require('@silverstripe/react-injector');

import Register from 'components/BackupCodes/Register';
import Login from 'components/BackupCodes/Login';
import Injector from 'lib/Injector';

export default () => {
  Injector.component.registerMany({
    BackupCodeRegister: Register,
    BackupCodeLogin: Login,
  });
};
