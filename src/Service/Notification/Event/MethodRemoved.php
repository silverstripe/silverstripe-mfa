<?php

namespace SilverStripe\MFA\Service\Notification\Event;

use SilverStripe\MFA\Service\Notification\Event;

class MethodRemoved extends Event
{
    private static $title = 'MFA method removed';

    private static $description = 'An authentication method was removed from your account';
}
