<?php

namespace Firesphere\BootstrapMFA\Authenticators;

use Firesphere\BootstrapMFA\Handlers\BootstrapMFALoginHandler;
use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Authenticator;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\PasswordEncryptor_NotFoundException;

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
            // Reset the subclass authenticator results
            $result = Injector::inst()->get(ValidationResult::class, false);

            /** @var Member $member */
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
