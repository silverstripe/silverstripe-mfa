<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Service;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\Security\Member;

/**
 * The RegisteredMethodManager service class facilitates the communication of Members and RegisteredMethod instances
 * in a reusable singleton.
 */
class RegisteredMethodManager
{
    use Extensible;
    use Injectable;

    private static $dependencies = [
        'NotificationService' => '%$' . Notification::class
    ];

    /**
     * @var Notification
     */
    protected $notification;

    public function setNotificationService(Notification $notification): RegisteredMethodManager
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * Get an authentication method object matching the given method from the given member. Returns null if the given
     * method could not be found attached to the Member
     *
     * @param Member&MemberExtension $member
     * @param MethodInterface $method
     * @return RegisteredMethod|null
     */
    public function getFromMember(Member $member, MethodInterface $method): ?RegisteredMethod
    {
        // Find the actual method registration data object from the member for the specified default authenticator
        foreach ($member->RegisteredMFAMethods() as $registeredMethod) {
            if ($registeredMethod->getMethod()->getURLSegment() === $method->getURLSegment()) {
                return $registeredMethod;
            }
        }

        return null;
    }

    /**
     * Fetch an existing RegisteredMethod object from the Member or make a new one, and then ensure it's associated
     * to the given Member
     *
     * @param Member&MemberExtension $member
     * @param MethodInterface $method
     * @param mixed $data
     * @return bool Whether the method was added/replace
     * @throws \SilverStripe\Core\Validation\ValidationException
     */
    public function registerForMember(Member $member, MethodInterface $method, $data = null): bool
    {
        if (empty($data)) {
            return false;
        }

        $registeredMethod = $this->getFromMember($member, $method)
            ?: RegisteredMethod::create(['MethodClassName' => get_class($method)]);

        $registeredMethod->Data = json_encode($data);
        $registeredMethod->write();

        // Add it to the member
        $member->RegisteredMFAMethods()->add($registeredMethod);

        // Define as the default, if none exists yet
        if (!$member->getDefaultRegisteredMethod()) {
            $member->setDefaultRegisteredMethod($registeredMethod);
            $member->write();
        }

        if (!MethodRegistry::create()->isBackupMethod($method)) {
            $this->notification->send(
                $member,
                'SilverStripe/MFA/Email/Notification_register',
                [
                    'subject' => _t(
                        RegisteredMethodManager::class . '.MFAADDED',
                        'A multi-factor authentication method was added to your account'
                    ),
                    'MethodName' => $method->getName(),
                ]
            );
        }

        $this->extend('onRegisterMethod', $member, $method);

        return true;
    }

    /**
     * Determines if a method can be removed
     *
     * By default this is false if MFA is required and the method is the last on the Member (besides the backup method)
     * but the funcation provides a hook point for extensibility e.g. if an site requires a particular method to be in
     * use by a subset of members - admins must use U2F but normal users can use TOTP.
     *
     * @param Member $member
     * @param MethodInterface $method
     * @return bool
     */
    public function canRemoveMethod(Member $member, MethodInterface $method): bool
    {
        $removable = true;
        $backupMethodClass = MethodRegistry::config()->get('default_backup_method');
        $remainingMethods = $member->RegisteredMFAMethods()
            ->filter('MethodClassName:Not', $backupMethodClass)
            ->count();
        $mfaIsRequired = Injector::inst()->get(EnforcementManager::class)->isMFARequired();

        // This is the last method (besides the backup method), and MFA is required
        if ($mfaIsRequired && $remainingMethods === 1) {
            $removable = false;
        }

        $this->extend(__FUNCTION__, $removable, $member, $method);

        return $removable;
    }

    /**
     * Delete a registration for the given method from the given member, provided it exists. This will also remove a
     * registered back-up method if it will leave the member with only the back-up method remaing
     *
     * @param Member&MemberExtension $member
     * @param MethodInterface $method
     * @return bool Returns false if the given method is not registered for the member
     * @throws \SilverStripe\Core\Validation\ValidationException
     */
    public function deleteFromMember(Member $member, MethodInterface $method): bool
    {
        if (!$method || !$this->canRemoveMethod($member, $method)) {
            return false;
        }

        $registeredMethod = $this->getFromMember($member, $method);
        $registeredMethod->delete();

        $backupRemovedToo = false;

        $backupMethod = MethodRegistry::config()->get('default_backup_method');
        $remainingMethods = $member->RegisteredMFAMethods()->count();
        if ($remainingMethods === 2) {
            // If there is only one other method (other than backup codes) then set that as the default method
            $remainingMethodExceptBackup = $member->RegisteredMFAMethods()
                ->filter('MethodClassName:Not', $backupMethod)
                ->first();

            if ($remainingMethodExceptBackup) {
                $member->setDefaultRegisteredMethod($remainingMethodExceptBackup);
                $member->write();
            }
        } elseif ($remainingMethods === 1) {
            // If there is only one method remaining, and that's the configured "backup" method - then delete that too
            $remainingMethod = $member->RegisteredMFAMethods()
                ->filter('MethodClassName', $backupMethod)
                ->first();

            if ($remainingMethod) {
                $remainingMethod->delete();
                $backupRemovedToo = true;
            }
        }

        $this->notification->send(
            $member,
            'SilverStripe/MFA/Email/Notification_removed',
            [
                'subject' => _t(
                    RegisteredMethodManager::class . '.MFAREMOVED',
                    'A multi-factor authentication method was removed from your account'
                ),
                'MethodName' => $method->getName(),
                'BackupAlsoRemoved' => $backupRemovedToo,
            ]
        );

        return true;
    }
}
