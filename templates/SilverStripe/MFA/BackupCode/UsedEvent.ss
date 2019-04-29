<p><%t SilverStripe\\MFA\\Service\\Notification\\Event\\MethodAdded_ss.HELLO 'Hi' %> $Member.FirstName,</p>

<p><%t SilverStripe\\MFA\BackupCode\\UsedEvent_ss.USED 'One of your backup codes has been used to gain access to your account.' %></p>

<p><%t SilverStripe\\MFA\BackupCode\\UsedEvent_ss.THEREARE 'You now have ' %> $CodesLeft <%t SilverStripe\\MFA\BackupCode\\UsedEvent_ss.REMAINING 'back up codes remaining.' %></p>

<p>
    <%t SilverStripe\\MFA\\Service\\NotificationManager.WASNTME 'If you did not undertake this action, plase contact your system administrator immediately' %>.
    <% if $SystemAdminContactDetails %>
    <br /><br />
    $SystemAdminContactDetails
    <% end_if %>
</p>
