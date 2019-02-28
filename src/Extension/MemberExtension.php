<?php
namespace SilverStripe\MFA\Extension;

use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\ORM\DataExtension;

/**
 * Extend Member to add relationship to registered methods and track some specific preferences
 *
 * @method RegisteredMethod[] RegisteredMFAMethods
 * @property string DefaultRegisteredMethod
 */
class MemberExtension extends DataExtension
{
    private static $has_many = [
        'RegisteredMFAMethods' => RegisteredMethod::class,
    ];

    private static $db = [
        'DefaultRegisteredMethod' => 'Varchar',
    ];
}
