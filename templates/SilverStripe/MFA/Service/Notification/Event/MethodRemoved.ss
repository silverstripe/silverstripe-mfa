<p><%t SilverStripe\\MFA\\Service\\Notification\\Event\\MethodRemoved_ss.HELLO 'Hi' %> $Member.FirstName,</p>

<p><%t SilverStripe\\MFA\\Service\\Notification\\Event\\MethodRemoved_ss.METHODREMOVED 'The Multi Factor Authentication (MFA) method' %> "$MethodName" <%t SilverStripe\\MFA\\Service\\Notification\\Event\\MethodRemoved_ss.FROMYOURACCOUNTFOR 'was removed from your account at' %> $AbsoluteBaseURL.</p>

<p>
    <%t SilverStripe\\MFA\\Service\\NotificationManager.WASNTME 'If you did not undertake this action, plase contact your system administrator immediately' %>.
    <% if $SystemAdminContactDetails %>
    <br /><br />
    $SystemAdminContactDetails
    <% end_if %>
</p>
