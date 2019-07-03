<p><% _t('SilverStripe\\MFA\\Email\\Notification.HELLO', 'Hi') %> $Member.Name,</p>

<p>
	<%t SilverStripe\\MFA\\Email\\Notification.REGISTERED 'You have removed the {method} authentication method from your account at {site}.' method=$MethodName site=$AbsoluteBaseURL %>
</p>

<p>
    <% _t('SilverStripe\\MFA\\Email\\Notification.NOTYOU', 'If you did not take this action, please contact your system administrator immediately.') %>
</p>
