<?php

namespace SilverStripe\MFA\Service\Tests;

use SapphireTest;
use SilverStripe\MFA\Service\Notification;
use Member;

class NotificationTest extends SapphireTest
{
    public function testCanBeDisabled()
    {
        Notification::config()->set('enabled', false);
        $result = Notification::create()->send($this->createMock(Member::class), 'foo');
        $this->assertFalse($result);
    }
}
