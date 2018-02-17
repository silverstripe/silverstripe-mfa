<?php

namespace Firesphere\BootstrapMFA\Authenticators;

use Firesphere\BootstrapMFA\Models\BackupCode;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;

class BootstrapMFAAuthenticator extends MemberAuthenticator
{

    /**
     * @param Member $member
     * @param string $token
     * @param ValidationResult|null $result
     * @return bool|Member
     */
    public function validateBackupCode($member, $token, &$result = null)
    {
        if (!$result) {
            $result = new ValidationResult();
        }
        $token = $member->encryptWithUserSettings($token);

        /** @var BackupCode $backupCode */
        $backupCode = BackupCode::getValidTokensForMember($member)
            ->filter(['Code' => $token])
            ->first();
        if ($backupCode && $backupCode->exists()) {
            $backupCode->expire();

            return $member;
        }

        $result->addError('Invalid token');

        return false;
    }
}
