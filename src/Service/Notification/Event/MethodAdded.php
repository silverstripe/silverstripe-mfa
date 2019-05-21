<?php

namespace SilverStripe\MFA\Service\Notification\Event;

use SilverStripe\MFA\Service\Notification\Event;

class MethodAdded extends Event
{
    private static $title = 'MFA method added';

    private static $description = 'A new authentication method was added to your account';
}
