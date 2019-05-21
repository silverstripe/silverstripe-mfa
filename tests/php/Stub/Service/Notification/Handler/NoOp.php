<?php

namespace SilverStripe\MFA\Tests\Stub\Service\Notification\Handler;

use SilverStripe\Dev\TestOnly;
use SilverStripe\MFA\Service\Notification\Event;
use SilverStripe\MFA\Service\Notification\HandlerInterface;
use SilverStripe\Security\Member;

class NoOp implements HandlerInterface, TestOnly
{
    private $count = 0;

    public function notify(Member $member, Event $event): bool
    {
        var_dump("why no singleton?");
        ++$this->count;
        return true;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
