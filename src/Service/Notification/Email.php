<?php

namespace SilverStripe\MFA\Service\Notification;

use SilverStripe\Control\Email\Email as EmailMessage;
use SilverStripe\Core\Extensible;
use SilverStripe\MFA\Service\NotificationEvent;
use SilverStripe\MFA\Service\NotificationInterface;
use SilverStripe\Security\Member;
use SilverStripe\View\SSViewer;

class Email implements NotificationInterface
{
    use Extensible;

    public function notify(Member $recipient, NotificationEvent $event): bool
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
        $this->extend('onBeforeSend', $email);
        return $email->send();
    }
}
