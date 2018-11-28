<?php

namespace Firesphere\BootstrapMFA\Authenticators;

use Firesphere\BootstrapMFA\Extensions\MemberExtension;
use Firesphere\BootstrapMFA\Handlers\BootstrapMFALoginHandler;
use Firesphere\BootstrapMFA\Models\BackupCode;
use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Authenticator;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\PasswordEncryptor_NotFoundException;
use SilverStripe\Security\Security;

/**
 * Class BootstrapMFAAuthenticator
 * It needs to be instantiable, therefore it can't be an Abstract.
 *
 * @package Firesphere\BootstrapMFA\Authenticators
 * @method string getTokenField() Stub for child implementations
 */
class BootstrapMFAAuthenticator extends MemberAuthenticator
{
    /**
     * Key for array to be stored in between steps in the session
     */
    const SESSION_KEY = 'MFALogin';

    /**
     * @return int
     */
    public function supportedServices()
    {
        // Bitwise-OR of all the supported services in this Authenticator, to make a bitmask
        return Authenticator::LOGIN | Authenticator::LOGOUT | Authenticator::CHANGE_PASSWORD
            | Authenticator::RESET_PASSWORD | Authenticator::CHECK_PASSWORD;
    }

    /**
     * @param Member|MemberExtension $member
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
        $hashingMethod = Security::config()->get('password_encryption_algorithm');
        $token = Security::encrypt_password($token, $member->BackupSalt, $hashingMethod);

        /** @var BootstrapMFAProvider $provider */
        $provider = Injector::inst()->get(BootstrapMFAProvider::class);
        $provider->setMember($member);

        /** @var DataList|BackupCode[] $backupCodes */
        $backupCodes = $provider->fetchToken($token['password']);

        // Stub, as the ValidationResult so far _could_ be valid, e.g. when not passed in
        $valid = false;
        foreach ($backupCodes as $backupCode) {
            /** @noinspection NotOptimalIfConditionsInspection */
            if ($backupCode &&
                $backupCode->exists() &&
                hash_equals($backupCode->Code, $token['password']) &&
                !$backupCode->Used
            ) {
                $backupCode->expire();
                // Reset the subclass authenticator results
                $result = ValidationResult::create();

                $valid = true;
            }
        }

        if ($valid) {
            return $member;
        }

        $member->registerFailedLogin();
        $result->addError(_t(self::class . '.INVALIDTOKEN', 'Invalid token'));

        return false;
    }

    /**
     * @param string $link
     * @return BootstrapMFALoginHandler|static
     */
    public function getLoginHandler($link)
    {
        return BootstrapMFALoginHandler::create($link, $this);
    }
}
