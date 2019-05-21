<?php

namespace SilverStripe\MFA\Service\Notification;

use Exception;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Member;

class Dispatch
{
    use Injectable;

    /**
     * @var HanderInterface[]
     */
    protected $handlers;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var Member
     */
    protected $recipient;

    public function __construct(Member $recipient, Event $event, array $handlers = [])
    {
        $this->setRecipient($recipient);
        $this->setEvent($event);
        $this->setHandlers($handlers);
    }

    public function setHandlers(array $handlers): self
    {
        $this->handlers = $handlers;
        return $this;
    }

    public function getHandlers(): array
    {
        return $this->handlers;
    }

    public function addHandler(HandlerInterface $handler, string $key = ''): self
    {
        if ($key) {
            $this->handlers[$key] = $handler;
        } else {
            $this->handlers[] = $handler;
        }
        return $this;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setRecipient(Member $recipient): self
    {
        $this->recipient = $recipient;
        return $this;
    }

    public function getRecipient(): Member
    {
        return $this->recipient;
    }

    public function send(): bool
    {
        $handlers = $this->getHandlers();
        $member = $this->getRecipient();
        $event = $this->getEvent();

        $allSentNoProblems = true;

        foreach ($handlers as $handler) {
            try {
                $allSentNoProblems &= $handler->notify($member, $event);
            } catch (Exception $e) {
                Injector::inst()->get(LoggerInterface::class)->info($e->getMessage());
            }
        }

        return $allSentNoProblems;
    }
}
