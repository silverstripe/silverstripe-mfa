<p><% _t('MFANotification.HELLO', 'Hi') %> $Member.Name,</p>

<p>
	<% sprintf(_t('MFANotification.USEDBACKUPCODE', 'A recovery code was used to gain access to your account at %s.'), $AbsoluteBaseURL) %>
</p>
<p>
	<% sprintf(_t('MFANotification.CODESLEFT', 'You now have %s codes remaining.'), $CodesRemaining) %>
</p>
<p>
    <% _t('MFANotification.NOTYOU', 'If you did not take this action, please contact your system administrator immediately.') %>
</p>
