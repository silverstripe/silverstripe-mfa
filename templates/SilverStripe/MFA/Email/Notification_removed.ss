<p><%t MFANotification.HELLO 'Hi' %> $Member.Name,</p>

<p>
	<%t MFANotification.REGISTERED 'You have removed the {method} authentication method from your account at {site}.' method=$MethodName site=$AbsoluteBaseURL %>
</p>

<p>
    <%t MFANotification.NOTYOU 'If you did not take this action, please contact your system administrator immediately.' %>
</p>
