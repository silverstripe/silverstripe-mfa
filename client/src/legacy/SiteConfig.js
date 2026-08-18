window.jQuery.entwine('ss', ($) => {
  $('[name="MFARequired"]').entwine({
    /**
     * Enable or disable the associated "grace period" date field
     */
    toggleGracePeriodField() {
      const isRequired = parseInt($('[name="MFARequired"]:checked').val(), 10);
      if (isRequired) {
        $('.mfa-settings__grace-period').removeAttr('disabled');
      } else {
        $('.mfa-settings__grace-period').attr('disabled', 'disabled');
      }
    },

    onchange() {
      this.toggleGracePeriodField();
    },

    /**
     * Ensure the "grace period" field state is correct when the page loads
     */
    onmatch() {
      this.toggleGracePeriodField();
    },
  });
});
