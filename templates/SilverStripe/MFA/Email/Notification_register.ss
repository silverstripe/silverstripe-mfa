<p><%t MFANotification.HELLO 'Hi' %> $Member.Name,</p>

<p>
	<%t MFANotification.REGISTERED 'You have successfully registered {method} as an extra layer of protection for your account at {site}.' method=$MethodName site=$AbsoluteBaseURL %>
</p>

<p>
    <%t MFANotification.NOTYOU 'If you did not take this action, please contact your system administrator immediately.' %>
</p>
