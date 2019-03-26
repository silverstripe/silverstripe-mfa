<% require javascript('silverstripe/mfa: client/dist/js/injector.js') %>
<% require javascript('silverstripe/admin: client/dist/js/i18n.js') %>
<% require javascript('silverstripe/mfa: client/dist/js/bundle.js') %>
<% require add_i18n_javascript('silverstripe/mfa: client/lang') %>
<% require css('silverstripe/mfa: client/dist/styles/bundle.css') %>

<div id="mfa-app" class="mfa__app" data-schemaurl="$Link('mfa/schema')"></div>
