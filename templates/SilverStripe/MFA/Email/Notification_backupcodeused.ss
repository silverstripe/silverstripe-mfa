<p><%t MFANotification.HELLO 'Hi' %> $Member.Name,</p>

<p>
	<%t MFANotification.USEDBACKUPCODE 'A recovery code was used to gain access to your account at {site}.' site=$AbsoluteBaseURL %>
</p>
<p>
	<%t MFANotification.CODESLEFT 'You now have {count} codes remaining.' count=$CodesRemaining %>
</p>
<p>
    <%t MFANotification.NOTYOU 'If you did not take this action, please contact your system administrator immediately.' %>
</p>
