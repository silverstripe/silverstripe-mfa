<?php

namespace SilverStripe\MFA\Tests\Stub\Service\Notification;

use SilverStripe\Dev\TestOnly;
use SilverStripe\MFA\Service\Notification\Event;
use SilverStripe\MFA\Service\Notification\Service as RealNotificationService;
use SilverStripe\Security\Member;

class Service extends RealNotificationService implements TestOnly
{
    public function sendNotifications(Member $member, Event $event): bool
    {
        return true;
    }
}
