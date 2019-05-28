<p><%t SilverStripe\\MFA\\Email\\Notification.HELLO 'Hi' %> $Member.Name,</p>

<p>
	<%t SilverStripe\\MFA\\Email\\Notification.USEDBACKUPCODE 'A recovery code was used to gain access to your account at {site}.' site=$AbsoluteBaseURL %>
</p>
<p>
	<%t SilverStripe\\MFA\\Email\\Notification.CODESLEFT 'You now have {count} codes remaining.' count=$CodesRemaining %>
</p>
<p>
    <%t SilverStripe\\MFA\\Email\\Notification.NOTYOU 'If you did not take this action, please contact your system administrator immediately.' %>
</p>
