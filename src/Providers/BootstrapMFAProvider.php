<?php

namespace Firesphere\BootstrapMFA\Providers;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Models\BackupCode;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;

class BootstrapMFAProvider implements MFAProvider
{
    protected $member;

    /**
     * @param string $token
     * @param null|ValidationResult $result
     * @return Member|bool
     * @throws \SilverStripe\ORM\ValidationException
     * @throws \SilverStripe\Security\PasswordEncryptor_NotFoundException
     */
    public function verifyToken($token, &$result = null)
    {
        if (!$result) {
            $result = new ValidationResult();
        }
        $member = $this->getMember();
        /** @var BootstrapMFAAuthenticator $authenticator */
        $authenticator = Injector::inst()->get(BootstrapMFAAuthenticator::class);

        return $authenticator->validateBackupCode($member, $token, $result);
    }

    /**
     * @return Member|null
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param Member $member
     */
    public function setMember($member)
    {
        $this->member = $member;
    }

    /**
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function updateTokens()
    {
        // Clear any possible tokens in the session, just to be sure
        Controller::curr()->getRequest()->getSession()->clear('tokens');

        if ($member = $this->getMember()) {
            /** @var DataList|BackupCode[] $expiredCodes */
            $expiredCodes = BackupCode::get()->filter(['MemberID' => $member->ID]);
            $expiredCodes->removeAll();

            BackupCode::generateTokensForMember($member);
        }
        // Fail silently
    }
}
