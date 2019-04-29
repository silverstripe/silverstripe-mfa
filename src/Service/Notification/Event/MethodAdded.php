<?php

namespace SilverStripe\MFA\Service\Notification\Event;

use SilverStripe\MFA\Service\Notification\Email;
use SilverStripe\MFA\Service\NotificationEvent;

class MethodAdded extends NotificationEvent
{
    private static $handlers = [
        Email::class,
    ];

    private static $title = 'MFA method added';

    private static $description = 'A new authentication method was added to your account';
}
