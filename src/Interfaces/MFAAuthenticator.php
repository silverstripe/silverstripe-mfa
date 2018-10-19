<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 19-Oct-18
 * Time: 13:47
 */

namespace Firesphere\BootstrapMFA\Interfaces;


use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;

interface MFAAuthenticator
{

    /**
     * Get the MFA form
     * @return mixed
     */
    public function getMFAForm();

    /**
     * Verify the MFA code
     *
     * @param Member $member
     * @param string $token
     * @param ValidationResult $result
     * @return mixed
     */
    public function verifyMFA($member, $token, &$result);

}