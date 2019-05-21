<?php

namespace SilverStripe\MFA\Service\Notification;

use SilverStripe\Core\Extensible;
use SilverStripe\Security\Member;

interface HandlerInterface
{
    public function notify(Member $member, Event $event): bool;
}
