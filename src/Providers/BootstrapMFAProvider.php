<?php

namespace Firesphere\BootstrapMFA\Providers;

use Firesphere\BootstrapMFA\Models\BackupCode;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;

class BootstrapMFAProvider implements MFAProvider
{
    protected $member;

    /**
     * @param string $token
     * @param null|ValidationResult $result
     * @return Member|bool
     * @throws ValidationException
     */
    public function verifyToken($token, &$result = null)
    {
        if (!$result) {
            $result = new ValidationResult();
        }
        $member = $this->getMember();

        /** @var BackupCode $backupCode */
        $backupCode = BackupCode::getValidTokensForMember($member)
            ->filter(['Code' => $token])
            ->first();

        if ($backupCode && $backupCode->exists()) {
            $backupCode->expire();

            /** @var Member $member */
            return $member;
        }

        $member->registerFailedLogin();
        $result->addError(_t(self::class . '.INVALIDTOKEN', 'Invalid token'));
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
