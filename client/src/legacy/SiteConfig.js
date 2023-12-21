window.jQuery.entwine('ss', ($) => {
  $('[name="MFARequired"]').entwine({
    /**
     * Set grace field enabled state based on whether it's enabled or not
     */
    setGraceFieldState() {
      const isRequired = parseInt(this.val(), 10);
      if (isRequired) {
        $('.mfa-settings__grace-period').removeAttr('disabled');
      } else {
        $('.mfa-settings__grace-period').attr('disabled', 'disabled');
      }
    },

    /**
     * Set correct grace field enabled state when value changes
     */
    onchange() {
      this.setGraceFieldState();
    },

    /**
     * Set correct grace field enabled state when form loads
     */
    onmatch() {
      this.setGraceFieldState();
    },
  });

  $('[name="MFAAppliesTo"]').entwine({
    /**
     * Set group field visibility based on which option is selected
     */
    setGroupFieldVisibility() {
      // Ignore radio buttons that aren't selected.
      if (!this.is(':checked')) {
        return;
      }

      // Toggle display
      const groupField = $('.js-mfa-group-restrictions');
      if (this.val() === 'everyone') {
        groupField.hide();
      } else {
        groupField.show();
      }
    },

    /**
     * Set group field visibility when selection changes
     */
    onchange() {
      this.setGroupFieldVisibility();
    },

    /**
     * Set group field visibility when form loads
     */
    onmatch() {
      this.setGroupFieldVisibility();
    },
  });
});
