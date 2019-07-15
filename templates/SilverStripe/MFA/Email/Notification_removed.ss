<p><% _t('MFANotification.HELLO', 'Hi') %> $Member.Name,</p>

<p>
	<% sprintf(_t('MFANotification.REGISTERED', 'You have removed the %s authentication method from your account at %s.'), $MethodName, $AbsoluteBaseURL) %>
</p>

<p>
    <% _t('MFANotification.NOTYOU', 'If you did not take this action, please contact your system administrator immediately.') %>
</p>
