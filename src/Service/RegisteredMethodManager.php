<?php declare(strict_types=1);

namespace SilverStripe\MFA\Service;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
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

    public function setNotificationService(Notification $notification): self
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
     * @throws \SilverStripe\ORM\ValidationException
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

        if (!MethodRegistry::create()->isBackupMethod($method)) {
            $this->notification->send(
                $member,
                'Email/MFA/Notification_register',
                [
                    'subject' => _t(
                        self::class . '.MFAADDED',
                        'A multi factor authentication method was added to your account'
                    ),
                    'MethodName' => $method->getRegisterHandler()->getName(),
                ]
            );
        }

        $this->extend('onRegisterMethod', $member, $method);

        return true;
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

        $backupRemovedToo = false;

        // If there is only one method remaining, and that's the configured "backup" method - then delete that too
        if ($member->RegisteredMFAMethods()->count() === 1
            && ($method = $member->RegisteredMFAMethods()->first())->MethodClassName
                === Config::inst()->get(MethodRegistry::class, 'default_backup_method')
        ) {
            $method->delete();
            $backupRemovedToo = true;
        }

        $this->notification->send(
            $member,
            'Email/MFA/Notification_removed',
            [
                'subject' => _t(
                    self::class . '.MFAREMOVED',
                    'A multi factor authentication method was removed from your account'
                ),
                'MethodName' => $method->getRegisterHandler()->getName(),
                'BackupAlsoRemoved' => $backupRemovedToo,
            ]
        );

        return true;
    }
}
