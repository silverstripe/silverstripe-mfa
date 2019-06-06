<?php

namespace SilverStripe\MFA\Service\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Service\Notification;
use SilverStripe\Security\Member;

class NotificationTest extends SapphireTest
{
    public function testCanBeDisabled()
    {
        Notification::config()->set('enabled', false);
        $result = Notification::create()->send($this->createMock(Member::class), 'foo');
        $this->assertFalse($result);
    }
}
