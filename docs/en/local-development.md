# Local development

When running development versions of a project using this module, you may want to disable multi-factor authentication
while you test other features. You can do this via YAML configuration, for example:

```yaml
---
Name: mydevconfig
Only:
  environment: dev
---
SilverStripe\MFA\Service\EnforcementManager:
  enabled: false
```

This will not redirect you to multi-factor authentication registration or verification screens when logging in.
