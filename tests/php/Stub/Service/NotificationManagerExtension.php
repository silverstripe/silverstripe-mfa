<?php

namespace SilverStripe\MFA\Tests\Stub\Service;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

class NotificationManagerExtension extends Extension implements TestOnly
{
    const mockHandlerInjectorName = 'SilverStripe\MFA\Tests\MockNotificationHandler';

    private $addMock = false;

    public function setAddMock(bool $toAddOrNotToAdd) : self
    {
        $this->addMock = $toAddOrNotToAdd;
        return $this;
    }

    public function onBeforeNotify($event, &$handlers)
    {
        $handlers = $this->addMock ? [self::mockHandlerInjectorName] : [];
    }
}
