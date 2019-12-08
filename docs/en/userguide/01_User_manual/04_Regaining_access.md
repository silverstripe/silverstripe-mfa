---
title: Regaining access when locked out
summary: Steps to recover your account if your primary MFA method is unavailable
---

# Regaining access when locked out

## Using backup codes

If your phone or security key is lost/damaged, you can use one of the backup
codes that were generated during the MFA setup process to log in.

Login to your SilverStripe CMS account with your email and password. When
prompted for your primary MFA method, press **Other options** and select
**Verify with backup code**.

![A screenshot of a user being prompted for a verification code during login, with the 'other options' button highlighted](../_images/01-04-1-login-prompt.png)

![A screenshot of the 'other options' dialog during login, with the 'Verify with backup code' option highlighted](../_images/01-04-2-other-options.png)

Enter a backup code, and press **Verify**. If the backup code is valid, you will
be logged into the CMS.

![A screenshot of a user being prompted to enter a backup code during login](../_images/01-04-3-recovery-code.png)

[hint]
If your primary MFA method is permanently lost, make sure you visit your profile
and remove or reset it before logging out. If you are running out of backup
codes, generate a new set to make sure you don't lose access to your account.
[/hint]

## Resetting your account

If your backup codes are also unavailable, you can contact your site
administrator to have them send you an email to reset your account. This will
enable you to reset both your password and MFA methods. See
[Resetting Accounts](../administrator_manual/resetting_accounts).

[CHILDREN]