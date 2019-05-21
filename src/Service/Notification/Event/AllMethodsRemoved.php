<?php

namespace SilverStripe\MFA\Service\Notification\Event;

use SilverStripe\MFA\Service\Notification\Event;

class AllMethodsRemoved extends Event
{
    private static $title = 'MFA removed';

    private static $description = 'All registered MFA methods have been removed from your account';
}
