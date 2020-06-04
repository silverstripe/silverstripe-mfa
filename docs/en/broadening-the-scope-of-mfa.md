# Broadening the scope of the MFA flow

## Default behaviour

The EnforcementManager class is responsible for making decisions regarding the multi factor authentication app flow, e.g. "should we redirect to the MFA section", "can the user skip MFA registration" etc.

By default, the MFA flow will only be presented during the login process to members who have access to some part of Silverstripe CMS or administration area.

## Applying MFA more widely

You can broaden the scope of the MFA flow so it applies to all members, regardless of whether they have CMS or administration privileges or not by setting the following configuration:

```yaml
SilverStripe\MFA\Service\EnforcementManager:
  requires_admin_access: false
```

However, note that users without access to the CMS will be unable to access their personal MFA settings and perform actions such as:

* adding additional MFA methods;
* removing, resetting, and changing default MFA methods; and
* resetting recovery codes.

A custom implementation would be required to provide this functionality. Otherwise it would be limited to Silverstripe CMS Administrators to [reset MFA settings](https://userhelp.silverstripe.org/en/4/optional_features/multi-factor_authentication/administrator_manual/resetting_accounts/) for a member on their behalf.
