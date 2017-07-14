<?php

namespace Firesphere\BootstrapMFA;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class BootstrapMFAProvider implements MFAProvider
{

    protected $member;

    public function setMember($member)
    {
        $this->member = $member;
    }

    public function getMember()
    {
        return $this->member ?: Member::create();
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


    public function updateTokens()
    {
        if($member = Security::getCurrentUser()) {
            /** @var DataList|BackupCode[] $expiredCodes */
            $expiredCodes = BackupCode::get()->filter(['MemberID' => $member->ID]);
            $expiredCodes->removeAll();

            BackupCode::generateTokensForMember($member);
        }
    }
}
