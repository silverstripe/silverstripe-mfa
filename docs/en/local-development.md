# Local development

When running development versions of a project using this module, multi-factor authentication
is disabled by default.

You can opt back into it via a config setting in your project.

```yaml
---
Name: mydevconfig
After: "#mfa-devconfig"
Only:
  environment: dev
---
SilverStripe\MFA\Service\EnforcementManager:
  enabled: true
```
