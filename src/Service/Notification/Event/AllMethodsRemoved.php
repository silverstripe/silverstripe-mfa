<?php

namespace SilverStripe\MFA\Service\Notification\Event;

use SilverStripe\MFA\Service\Notification\Email;
use SilverStripe\MFA\Service\NotificationEvent;

class AllMethodsRemoved extends NotificationEvent
{
    private static $handlers = [
        Email::class,
    ];

    private static $title = 'MFA removed';

    private static $description = 'All registered MFA methods have been removed from your account';
}
