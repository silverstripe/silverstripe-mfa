<?php

namespace SilverStripe\MFA\BackupCode;

use SilverStripe\MFA\Service\Notification\Email;
use SilverStripe\MFA\Service\NotificationEvent;

class UsedEvent extends NotificationEvent
{
    private static $handlers = [
        Email::class,
    ];

    private static $title = "Backup code used";

    private static $description = 'One of your backup codes was used to log into your account';
}
