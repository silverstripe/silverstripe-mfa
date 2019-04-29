<p><%t SilverStripe\\MFA\\Service\\Notification\\Event\\AllMethodsRemoved_ss.HELLO 'Hi' %> $Member.FirstName,</p>

<p><%t SilverStripe\\MFA\\Service\\Notification\\Event\\AllMethodsRemoved_ss.METHODREMOVED 'All Multi Factor Authentication (MFA) methods were removed from your account for' %> $AbsoluteBaseURL.</p>
<p><%t SilverStripe\\MFA\\Service\\Notification\\Event\\AllMethodsRemoved_ss.SINGLEFACTOR 'Your account is now only password protected. It is recommended to re-enable a second factor as soon as is convenient.' %></p>
<p>
    <%t SilverStripe\\MFA\\Service\\NotificationManager.WASNTME 'If you did not undertake this action, plase contact your system administrator immediately' %>.
    <% if $SystemAdminContactDetails %>
    <br /><br />
    $SystemAdminContactDetails
    <% end_if %>
</p>
