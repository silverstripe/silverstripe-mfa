<?php

namespace SilverStripe\MFA\Service;

use SilverStripe\Core\Extensible;
use SilverStripe\Security\Member;

interface NotificationInterface
{
    public function notify(Member $member, NotificationEvent $event): bool;
}
