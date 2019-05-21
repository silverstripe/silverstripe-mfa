<?php

namespace SilverStripe\MFA\Service\Notification;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\BackupCode\CodeConsumed;
use SilverStripe\MFA\Service\Notification\Event\AllMethodsRemoved;
use SilverStripe\MFA\Service\Notification\Event\MethodAdded;
use SilverStripe\MFA\Service\Notification\Event\MethodRemoved;
use SilverStripe\MFA\Service\Notification\Handler\Email;
use SilverStripe\Security\Member;

class Service
{
    use Configurable;
    use Extensible;
    use Injectable;

    /**
     * Handlers configured to send notifications about this event types.
     * Should be an array keyed by {@see Event} class names, each consisting of
     * - an array of {@see NotificationInterface} implementation class names
     * - a simple string (still a class name of the {@see NotificationInterface}).
     *
     * @config
     * @var string[]
     */
    private static $defaultHandlers = [
        MethodAdded::class => [Email::class],
        MethodRemoved::class => [Email::class],
        AllMethodsRemoved::class => [Email::class],
        CodeConsumed::class => [Email::class],
    ];

    public static function get_default_handlers_for(Event $event): array
    {
        $handlers = static::config()->get('defaultHandlers') ?: [];
        $eventClass = get_class($event);
        $handlers = array_key_exists($eventClass, $handlers) ? $handlers[$eventClass] : [];
        if (is_string($handlers)) {
            $handlers = [$handlers];
        }
        return array_map([Injector::inst(), 'get'], $handlers);
    }

    public function dispatchNotifications(Member $member, Event $event): bool
    {
        $handlers = static::get_default_handlers_for($event);
        $dispatch = Dispatch::create($member, $event, $handlers);

        $this->extend('onBeforeNotify', $dispatch);

        return $dispatch->send();
    }
}
