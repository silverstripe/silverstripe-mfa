import Injector from 'lib/Injector'; // eslint-disable-line
import MFARegister from 'components/Register';
import RegisteredMFAMethodListField from 'components/FormField/RegisteredMFAMethodListField/RegisteredMFAMethodListField';
import baseRegisterComponents from '../registerComponents';

export default () => {
  baseRegisterComponents();

  Injector.component.registerMany({
    MFARegister,
    RegisteredMFAMethodListField
  });
};
