import Injector from 'lib/Injector'; // eslint-disable-line
import baseRegisterComponents from '../registerComponents';
import MFARegister from 'components/Register';
import RegisteredMFAMethodListField from 'components/FormField/RegisteredMFAMethodListField/RegisteredMFAMethodListField';


export default () => {
  baseRegisterComponents();

  Injector.component.registerMany({
    MFARegister,
    RegisteredMFAMethodListField
  });
};
