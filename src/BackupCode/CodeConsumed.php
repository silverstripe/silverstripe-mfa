<?php

namespace SilverStripe\MFA\BackupCode;

use SilverStripe\MFA\Service\Notification\Event;

class CodeConsumed extends Event
{
    private static $title = "Backup code used";

    private static $description = 'One of your backup codes was used to log into your account';
}
