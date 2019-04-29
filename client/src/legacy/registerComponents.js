import Injector from 'lib/Injector'; // eslint-disable-line
import RegisteredMFAMethodListField from '../components/FormField/RegisteredMFAMethodListField/RegisteredMFAMethodListField';

export default () => {
  Injector.component.register('RegisteredMFAMethodListField', RegisteredMFAMethodListField);
};
