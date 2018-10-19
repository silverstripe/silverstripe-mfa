<?php

namespace Firesphere\BootstrapMFA\Authenticators;

use Firesphere\BootstrapMFA\Handlers\BootstrapMFALoginHandler;
use Firesphere\BootstrapMFA\Interfaces\MFAAuthenticator;
use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\PasswordEncryptor_NotFoundException;

/**
 * Class BootstrapMFAAuthenticator
 * It needs to be instantiable, therefore it can't be an Abstract.
 *
 * @todo Interface!
 *
 * @package Firesphere\BootstrapMFA\Authenticators
 */
class BootstrapMFAAuthenticator extends MemberAuthenticator implements MFAAuthenticator
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

    /**
     * @param $member
     * @param $token
     * @param $result
     * @throws \Exception
     */
    public function verifyMFA($member, $token, &$result)
    {
        throw new \LogicException('No token verification implemented');
    }

    /**
     * @throws \Exception
     */
    public function getMFAForm()
    {
        throw new \LogicException('No MFA Form implementation found');
    }
}
