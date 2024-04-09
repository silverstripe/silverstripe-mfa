---
title: Local development
---

# Local development

When running development versions of a project using this module, you may want to disable multi-factor authentication
while you test other features. This will not redirect you to multi-factor authentication registration or verification screens when logging in.

The easiest way is to set an [environment variable](https://docs.silverstripe.org/en/developer_guides/configuration/environment_variables/):

```text
BYPASS_MFA=1
```

Alternatively, YAML configuration affords you more control over the conditions:

```yml
---
Name: mydevconfig
Only:
  environment: dev
---
SilverStripe\MFA\Service\EnforcementManager:
  enabled: false
```
