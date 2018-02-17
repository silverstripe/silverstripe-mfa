<?php

namespace Firesphere\BootstrapMFA\Providers;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Models\BackupCode;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class BootstrapMFAProvider implements MFAProvider
{
    protected $member;

    /**
     * @param Member $member
     */
    public function setMember($member)
    {
        $this->member = $member;
    }

    /**
     * @return Member|null
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param string $token
     * @param null|ValidationResult $result
     * @return Member|bool
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
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function updateTokens()
    {
        if ($member = $this->getMember()) {
            /** @var DataList|BackupCode[] $expiredCodes */
            $expiredCodes = BackupCode::get()->filter(['MemberID' => $member->ID]);
            $expiredCodes->removeAll();

            BackupCode::generateTokensForMember($member);
        }
        // Fail silently
    }
}
