<?php

namespace SilverStripe\MFA\Service;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Service\NotificationEvent;
use SilverStripe\Security\Member;

class NotificationManager
{
    use Configurable;
    use Extensible;
    use Injectable;

    public function sendNotifications(Member $member, NotificationEvent $event): bool
    {
        $handlers = $event->config()->get('handlers') ?: [];

        if (is_string($handlers)) {
            $handlers = [$handlers];
        }

        $this->extend('onBeforeNotify', $event, $handlers);

        foreach ($handlers as $handlerType) {
            $handler = Injector::inst()->get($handlerType);
            $handler->notify($member, $event);
        }

        return true;
    }
}
