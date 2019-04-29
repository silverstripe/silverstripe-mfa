<p><%t SilverStripe\\MFA\\Service\\Notification\\Event\\MethodAdded_ss.HELLO 'Hi' %> $Member.FirstName,</p>

<p><%t SilverStripe\\MFA\\Service\\Notification\\Event\\MethodAdded_ss.ANEWMETHOD 'A new Multi Factor Authentication (MFA) method' %> "$MethodName" <%t SilverStripe\\MFA\\Service\\Notification\\Email_add_ss.TOYOURACCOUNTFOR 'was added to your account for' %> $AbsoluteBaseURL.</p>

<p>
    <%t SilverStripe\\MFA\\Service\\NotificationManager.WASNTME 'If you did not undertake this action, plase contact your system administrator immediately' %>.
    <% if $SystemAdminContactDetails %>
    <br /><br />
    $SystemAdminContactDetails
    <% end_if %>
</p>
