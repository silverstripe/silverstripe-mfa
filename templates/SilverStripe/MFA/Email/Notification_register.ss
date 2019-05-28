<p><%t SilverStripe\\MFA\\Email\\Notification.HELLO 'Hi' %> $Member.Name,</p>

<p>
	<%t SilverStripe\\MFA\\Email\\Notification.REGISTERED 'You have successfully registered {method} as an extra layer of protection for your account at {site}.' method=$MethodName site=$AbsoluteBaseURL %>
</p>

<p>
    <%t SilverStripe\\MFA\\Email\\Notification.NOTYOU 'If you did not take this action, please contact your system administrator immediately.' %>
</p>
