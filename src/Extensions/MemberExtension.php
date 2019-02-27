<?php
namespace SilverStripe\MFA\Extensions;

use SilverStripe\MFA\Model\AuthenticationMethod;
use SilverStripe\ORM\DataExtension;

/**
 * Extend Member to add relationship to authentication methods and track some specific preferences
 *
 * @package SilverStripe\MFA
 * @method AuthenticationMethod[] AuthenticationMethods
 * @property string DefaultAuthenticationMethod
 */
class MemberExtension extends DataExtension
{
    private static $has_many = [
        'AuthenticationMethods' => AuthenticationMethod::class,
    ];

    private static $db = [
        'DefaultAuthenticationMethod' => 'Varchar',
    ];
}
