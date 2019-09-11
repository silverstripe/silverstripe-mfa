<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Extension\AccountReset;

use FieldList;
use DataExtension;
use DataObject;
use SS_Datetime as DBDatetime;
use Member;
use RandomGenerator;

/**
 * Provides DB columns / methods for account resets on Members
 *
 * @package SilverStripe\MFA\Extension
 * @property Member&MemberExtension owner
 * @property string AccountResetHash
 * @property DBDatetime AccountResetExpired
 */
class MemberExtension extends DataExtension
{
    private static $db = [
        'AccountResetHash' => 'Varchar(160)',
        'AccountResetExpired' => 'SS_Datetime',
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(['AccountResetHash', 'AccountResetExpired']);

        return $fields;
    }

    /**
     * Mirrors the implementation in Member::generateAutologinTokenAndStoreHash(),
     * but against the AccountReset fields.
     *
     * @return string
     */
    public function generateAccountResetTokenAndStoreHash(): string
    {
        $lifetime = $this->owner->config()->auto_login_token_lifetime;
        $generator = new RandomGenerator();

        do {
            $token = $generator->randomToken();
            $hash = $this->owner->encryptWithUserSettings($token);
        } while (
            DataObject::get_one(Member::class, [
            '"Member"."AccountResetHash"' => $hash,
            ])
        );

        $expiry = DBDatetime::create();
        $expiry->setValue(
            intval(DBDatetime::now()->Format('U')) + $lifetime
        );

        $this->owner->AccountResetHash = $hash;
        $this->owner->AccountResetExpired = $expiry->getValue();

        $this->owner->write();

        return $token;
    }

    /**
     * Based on Member::validateAutoLoginToken() and Member::member_from_autologinhash().
     *
     * @param string $token
     * @return bool
     */
    public function verifyAccountResetToken(string $token): bool
    {
        if (!$this->owner->exists()) {
            return false;
        }

        $hash = $this->owner->encryptWithUserSettings($token);

        return (
            $this->owner->AccountResetHash === $hash &&
            $this->owner->AccountResetExpired >= DBDatetime::now()->getValue()
        );
    }
}
