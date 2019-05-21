<?php

namespace SilverStripe\MFA\Tests\Stub\Service\Notification;

use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\TestOnly;
use SilverStripe\MFA\Tests\Stub\Service\Notification\Handler\NoOp;

class ServiceExtension extends Extension implements TestOnly
{
    const HANDLER = NoOp::class;

    public function onBeforeNotify($dispatch)
    {
        var_dump('yeah mate');
        $dispatch->setHandlers([Injector::inst()->get(self::HANDLER)]);
    }
}
