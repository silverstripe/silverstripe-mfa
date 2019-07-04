<p><% _t('Notification.HELLO', 'Hi') %> $Member.Name,</p>

<p>
	<%t Notification.REGISTERED 'You have removed the {method} authentication method from your account at {site}.' method=$MethodName site=$AbsoluteBaseURL %>
</p>

<p>
    <% _t('Notification.NOTYOU', 'If you did not take this action, please contact your system administrator immediately.') %>
</p>
