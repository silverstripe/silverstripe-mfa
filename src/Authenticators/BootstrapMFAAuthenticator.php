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
     * @return void|Member
     * @throws \SilverStripe\ORM\ValidationException
     * @throws \SilverStripe\Security\PasswordEncryptor_NotFoundException
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

        $member->registerFailedLogin();
        $result->addError('Invalid token');
    }
}
