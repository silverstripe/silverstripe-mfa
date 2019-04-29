<?php

namespace SilverStripe\MFA\Service;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\Notification\Event\AllMethodsRemoved;
use SilverStripe\MFA\Service\Notification\Event\MethodAdded;
use SilverStripe\MFA\Service\Notification\Event\MethodRemoved;
use SilverStripe\MFA\Service\NotificationManager;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * The RegisteredMethodManager service class facilitates the communication of Members and RegisteredMethod instances
 * in a reusable singleton.
 */
class RegisteredMethodManager
{
    use Extensible;
    use Injectable;

    /**
     * Get an authentication method object matching the given method from the given member. Returns null if the given
     * method could not be found attached to the Member
     *
     * @param Member&MemberExtension $member
     * @param MethodInterface $method
     * @return RegisteredMethod|null
     */
    public function getFromMember(Member $member, MethodInterface $method)
    {
        // Find the actual method registration data object from the member for the specified default authenticator
        foreach ($member->RegisteredMFAMethods() as $registeredMethod) {
            if ($registeredMethod->getMethod()->getURLSegment() === $method->getURLSegment()) {
                return $registeredMethod;
            }
        }
    }

    /**
     * Fetch an existing RegisteredMethod object from the Member or make a new one, and then ensure it's associated
     * to the given Member
     *
     * @param Member&MemberExtension $member
     * @param MethodInterface $method
     * @param mixed $data
     */
    public function registerForMember(Member $member, MethodInterface $method, $data = null)
    {
        if (empty($data)) {
            return;
        }

        $registeredMethod = $this->getFromMember($member, $method)
            ?: RegisteredMethod::create(['MethodClassName' => get_class($method)]);

        $registeredMethod->Data = json_encode($data);
        $registeredMethod->write();

        // Add it to the member
        $member->RegisteredMFAMethods()->add($registeredMethod);

        if (!MethodRegistry::create()->isBackupMethod($method)) {
            $data = ['MethodName' => $method->getRegisterHandler()->getName()];
            $notificationEvent = MethodAdded::create($member, $data);
            NotificationManager::create()->sendNotifications($member, $notificationEvent);
        }

        $this->extend('onRegisterMethod', $member, $method);
    }

    /**
     * Delete a registration for the given method from the given member, provided it exists. This will also remove a
     * registered back-up method if it will leave the member with only the back-up method remaing
     *
     * @param Member&MemberExtension $member
     * @param MethodInterface $method
     * @return bool Returns false if the given method is not registered for the member
     */
    public function deleteFromMember(Member $member, MethodInterface $method): bool
    {
        $method = $this->getFromMember($member, $method);

        if (!$method) {
            return false;
        }

        $method->delete();

        $data = ['MethodName' => $method->getRegisterHandler()->getName()];
        $event = MethodRemoved::create($member, $data);

        // If there is only one method remaining, and that's the configured "backup" method - then delete that too
        if ($member->RegisteredMFAMethods()->count() === 1
            && ($method = $member->RegisteredMFAMethods()->first())->MethodClassName
                === Config::inst()->get(MethodRegistry::class, 'default_backup_method')
        ) {
            $method->delete();
            $event = AllMethodsRemoved::create($member);
        }

        NotificationManager::create()->sendNotifications($member, $event);

        return true;
    }
}
