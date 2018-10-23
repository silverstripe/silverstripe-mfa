<?php

namespace Firesphere\BootstrapMFA\Providers;

use Firesphere\BootstrapMFA\Models\BackupCode;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Member;

class BootstrapMFAProvider
{
    protected $member;

    /**
     * @param string $token
     * @return null|BackupCode
     */
    public function fetchToken($token)
    {
        $member = $this->getMember();

        /** @var BackupCode $backupCode */
        $backupCode = BackupCode::getValidTokensForMember($member)
            ->filter(['Code' => $token])
            ->first();

        return $backupCode;
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
     * @throws ValidationException
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
