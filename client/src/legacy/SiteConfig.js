/* global window */
const hiddenClass = 'mfa-settings--hidden';

window.jQuery.entwine('ss', ($) => {
  $('[name="MFAEnabled"]').entwine({
    /**
     * Hide and show the radio list for "optional" or "required" when enabling MFA
     */
    onchange() {
      // Hide or show sub-fields dependent on the "required" field
      $('[name="MFARequired"]').trigger('change');

      if (this.is(':checked')) {
        $('.mfa-settings__required').removeClass(hiddenClass);
      } else {
        $('.mfa-settings__required').addClass(hiddenClass);
        $('.mfa-settings__grace-period').addClass(hiddenClass);
      }
    },

    /**
     * Ensure the settings hide/show handlers wake up when the page does
     */
    onmatch() {
      this.trigger('change');
    },
  });

  $('[name="MFARequired"]').entwine({
    /**
     * Hide or show the associated "grace period" date field
     */
    onchange() {
      if (!this.is(':checked')) {
        return;
      }

      const isRequired = parseInt(this.val(), 10);
      if (isRequired) {
        $('.mfa-settings__grace-period').removeClass(hiddenClass);
      } else {
        $('.mfa-settings__grace-period').addClass(hiddenClass);
      }
    },

    /**
     * Ensure the "grace period" hide/show handlers wake up when the page does
     */
    onmatch() {
      this.trigger('change');
    },
  });
});
