<p><% _t('MFANotification.HELLO', 'Hi') %> $Member.Name,</p>

<p>
	<% sprintf(_t('MFANotification.REGISTERED', 'You have successfully registered %s as an extra layer of protection for your account at %s.'), $MethodName, $AbsoluteBaseURL) %>
</p>

<p>
    <% _t('MFANotification.NOTYOU', 'If you did not take this action, please contact your system administrator immediately.') %>
</p>
