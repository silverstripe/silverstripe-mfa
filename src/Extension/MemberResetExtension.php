<?php

namespace SilverStripe\MFA\Extension;

use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Member;
use SilverStripe\Security\PasswordEncryptor_NotFoundException;
use SilverStripe\Security\RandomGenerator;

/**
 * Provides DB columns / methods for account resets on Members
 *
 * @package SilverStripe\MFA\Extension
 * @property Member owner
 */
class MemberResetExtension extends DataExtension
{
    private static $db = [
        'AccountResetHash' => 'Varchar(160)',
        'AccountResetExpired' => 'Datetime',
    ];

    /**
     * Mirrors the implementation in Member::generateAutologinTokenAndStoreHash(),
     * but against the AccountReset fields.
     *
     * @return string
     * @throws PasswordEncryptor_NotFoundException
     * @throws ValidationException
     */
    public function generateAccountResetTokenAndStoreHash(): string
    {
        $lifetime = $this->owner->config()->auto_login_token_lifetime;

        do {
            $generator = new RandomGenerator();
            $token = $generator->randomToken();
            $hash = $this->owner->encryptWithUserSettings($token);
        } while (DataObject::get_one(Member::class, [
            '"Member"."AccountResetHash"' => $hash,
        ]));

        $this->owner->AccountResetHash = $hash;
        $this->owner->AccountResetExpired = date('Y-m-d H:i:s', time() + $lifetime);

        $this->owner->write();

        return $token;
    }

    /**
     * Based on Member::validateAutoLoginToken() and Member::member_from_autologinhash().
     *
     * @param string $token
     * @return Member
     * @throws PasswordEncryptor_NotFoundException
     */
    public function getMemberByAccountResetToken(string $token): ?Member
    {
        $hash = $this->owner->encryptWithUserSettings($token);

        /** @var Member $member */
        $member = Member::get()->filter([
            'AutoLoginHash' => $hash,
            'AutoLoginExpired:GreaterThan' => DBDatetime::now()->getValue(),
        ])->first();

        return $member;
    }
}