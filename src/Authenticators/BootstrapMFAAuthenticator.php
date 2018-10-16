<?php

namespace Firesphere\BootstrapMFA\Authenticators;

use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\PasswordEncryptor_NotFoundException;

/**
 * Class BootstrapMFAAuthenticator
 * @package Firesphere\BootstrapMFA\Authenticators
 */
class BootstrapMFAAuthenticator extends MemberAuthenticator
{
    /**
     * Key for array to be stored in between steps in the session
     */
    const SESSION_KEY = 'MFALogin';

    /**
     * @param Member $member
     * @param string $token
     * @param ValidationResult|null $result
     * @return bool|Member
     * @throws ValidationException
     * @throws PasswordEncryptor_NotFoundException
     */
    public function validateBackupCode($member, $token, &$result = null)
    {
        if (!$result) {
            $result = new ValidationResult();
        }
        $token = $member->encryptWithUserSettings($token);

        /** @var BootstrapMFAProvider $provider */
        $provider = Injector::inst()->get(BootstrapMFAProvider::class);
        $provider->setMember($member);

        $backupCode = $provider->fetchToken($token);

        if ($backupCode && $backupCode->exists()) {
            $backupCode->expire();
            $result = Injector::inst()->get(ValidationResult::class, false);

            /** @var Member $member */
            return $member;
        }

        $member->registerFailedLogin();
        $result->addError(_t(self::class . '.INVALIDTOKEN', 'Invalid token'));

        return false;
    }
}
