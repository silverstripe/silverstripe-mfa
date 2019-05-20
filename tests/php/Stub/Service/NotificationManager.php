<?php

namespace SilverStripe\MFA\Tests\Stub\Service;

use SilverStripe\Dev\TestOnly;
use SilverStripe\MFA\Service\NotificationEvent;
use SilverStripe\MFA\Service\NotificationManager as RealNotificationManager;
use SilverStripe\Security\Member;

class NotificationManager extends RealNotificationManager implements TestOnly
{
    public function sendNotifications(Member $member, NotificationEvent $event): bool
    {
        return true;
    }
}
