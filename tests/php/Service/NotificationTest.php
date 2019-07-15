<?php

namespace SilverStripe\MFA\Service\Tests;

use Config;
use SapphireTest;
use SilverStripe\MFA\Service\Notification;
use Member;

class NotificationTest extends SapphireTest
{
    public function testCanBeDisabled()
    {
        Config::inst()->remove(Notification::class, 'enabled');
        Config::inst()->update(Notification::class, 'enabled', false);
        ;
        $result = Notification::create()->send($this->createMock(Member::class), 'foo');
        $this->assertFalse($result);
    }
}
