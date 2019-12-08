---
title: Managing your MFA settings
summary: Steps for adding, removing and resetting MFA methods
---

# Managing your MFA settings

You can add, reset, or remove MFA methods in your CMS Profile, which can be
visited by clicking on your name in the top left area of the CMS interface. The
MFA settings are protected by "sudo mode", which means you may need to
re-enter your password to access them.

![A screenshot of a user being prompted to enter their password before modifying their MFA settings in the CMS](../_images/01-05-1-sudo-mode.png)

## Adding additional methods

1. Go to your profile page, and find the Multi-factor authentication settings
   area. If you are prompted for your password, enter it.
2. Press **Add another MFA method**. If prompted, pick which method to set up.
3. Follow the relevant guide for setting up the method:
   - [Authenticator app](using_authenticator_apps)
   - [Security key](using_security_keys)

![A screenshot of the MFA settings field on a user's CMS profile, with the 'Add another MFA method' highlighted](../_images/01-05-2-add-method.png)

## Removing, resetting and changing default MFA methods

1. Go to your profile page, and find the Multi-factor authentication settings
   area. If you are prompted for your password, enter it.
2. Find the method you want to modify, and press the action beneath it that you
   want to take. Actions include:
   - **Remove:** This will delete the method. This action is only available if
     you have multiple methods registered, or if MFA is optional for your site.
   - **Reset:** Takes you through the setup process for the method, replacing
     the existing method. Useful if you lose access to your old key/device.
   - **Set as default method:** Only visible if you have multiple methods, this
     will toggle which method is displayed first during login.

![A screenshot of the MFA settings field on a user's CMS profile, with the actions related to a registered method highlighted](../_images/01-05-3-modify-method.png)

## Resetting recovery codes

1. Go to your profile page, and find the Multi-factor authentication settings
   area. If you are prompted for your password, enter it.
2. Find the Backup codes method, and press the **Reset** action.
3. You will be presented with a new set of backup codes, which you should store
   in a safe place.

![A screenshot of the dialog presented when a user presses the 'Reset' action on their CMS profile](../_images/01-05-4-reset-recovery-codes.png)

[CHILDREN]