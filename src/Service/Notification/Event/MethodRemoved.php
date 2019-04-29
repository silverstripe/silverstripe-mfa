<?php

namespace SilverStripe\MFA\Service\Notification\Event;

use SilverStripe\MFA\Service\Notification\Email;
use SilverStripe\MFA\Service\NotificationEvent;

class MethodRemoved extends NotificationEvent
{
    private static $handlers = [
        Email::class,
    ];

    private static $title = 'MFA method removed';

    private static $description = 'An authentication method was removed from your account';
}
