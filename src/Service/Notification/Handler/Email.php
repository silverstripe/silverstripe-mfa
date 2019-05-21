<?php

namespace SilverStripe\MFA\Service\Notification\Handler;

use SilverStripe\Control\Email\Email as EmailMessage;
use SilverStripe\Core\Extensible;
use SilverStripe\MFA\Service\Notification\Event;
use SilverStripe\MFA\Service\Notification\HandlerInterface;
use SilverStripe\Security\Member;
use SilverStripe\View\SSViewer;

class Email implements HandlerInterface
{
    use Extensible;

    public function notify(Member $recipient, Event $event): bool
    {
        $data = $event->getData();
        $from = $event->getDatum('from');
        $replyTo = $event->getDatum('replyTo');
        $email = EmailMessage::create()
            ->setTo($recipient->Email)
            ->setSubject($event->getDescription())
            ->setBody($event->renderWith($event->getTemplates(static::class)));
        if ($from) {
            $email->setFrom($from);
        }
        if ($replyTo) {
            $email->setReplyTo($replyTo);
        }
        $this->extend('onBeforeSend', $email, $event);
        return $email->send();
    }
}
