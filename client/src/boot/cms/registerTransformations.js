import Injector from 'lib/Injector';
import WithSudoMode from 'containers/SudoMode/SudoMode';

export default () => {
  // Apply sudo mode to the mfa list field
  Injector.transform('apply-sudo-mode-to-mfa', (updater) => {
    updater.component('RegisteredMFAMethodListField', WithSudoMode);
  });
};
