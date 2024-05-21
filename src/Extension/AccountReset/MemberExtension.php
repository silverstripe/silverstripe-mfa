<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Extension\AccountReset;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\Security\RandomGenerator;

/**
 * Provides DB columns / methods for account resets on Members
 *
 * @property string $AccountResetHash
 * @property DBDatetime $AccountResetExpired
 *
 * @extends DataExtension<Member&static>
 */
class MemberExtension extends DataExtension
{
    private static $db = [
        'AccountResetHash' => 'Varchar(160)',
        'AccountResetExpired' => 'Datetime',
    ];

    protected function updateCMSFields(FieldList $fields)
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

        $this->owner->AccountResetHash = $hash;
        $this->owner->AccountResetExpired = DBDatetime::create()->setValue(
            DBDatetime::now()->getTimestamp() + $lifetime
        )->Rfc2822();

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
