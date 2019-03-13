<?php
namespace SilverStripe\MFA\Extension;

use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;

/**
 * Extend Member to add relationship to registered methods and track some specific preferences
 *
 * @method RegisteredMethod[]|HasManyList RegisteredMFAMethods
 * @property MethodInterface DefaultRegisteredMethod
 * @property string DefaultRegisteredMethodID
 * @property bool HasSkippedMFARegistration
 * @property Member|MemberExtension owner
 */
class MemberExtension extends DataExtension
{
    private static $has_many = [
        'RegisteredMFAMethods' => RegisteredMethod::class,
    ];

    private static $db = [
        'DefaultRegisteredMethodID' => 'Int',
        'HasSkippedMFARegistration' => 'Boolean',
    ];

    /**
     * Accessor for the `DefaultRegisteredMethod` property
     *
     * This is replicating the usual functionality of a has_one relation but does it like this so we can ensure the same
     * instance of the MethodInterface is provided regardless if you access it through the has_one or the has_many.
     *
     * @return MethodInterface
     */
    public function getDefaultRegisteredMethod()
    {
        return $this->owner->RegisteredMFAMethods()->byId($this->owner->DefaultRegisteredMethodID);
    }
}
