window.jQuery.entwine('ss', ($) => {
  $('[name="MFARequired"]').entwine({
    /**
     * Enable or disable the associated "grace period" date field
     */
    onchange() {
      const isRequired = parseInt(this.val(), 10);
      if (isRequired) {
        $('.mfa-settings__grace-period').removeAttr('disabled');
      } else {
        $('.mfa-settings__grace-period').attr('disabled', 'disabled');
      }
    },

    /**
     * Ensure the "grace period" hide/show handlers wake up when the page does
     */
    onmatch() {
      this.onchange();
    },
  });
});
