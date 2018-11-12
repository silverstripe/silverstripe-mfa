<?php

namespace Firesphere\BootstrapMFA\Interfaces;

use Firesphere\BootstrapMFA\Forms\BootstrapMFALoginForm;
use Firesphere\BootstrapMFA\Handlers\BootstrapMFALoginHandler;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\ValidationResult;

interface MFAAuthenticator
{

    /**
     * Get the MFA form
     *
     * @param BootstrapMFALoginHandler $controller
     * @param string $name
     * @return BootstrapMFALoginForm
     */
    public function getMFAForm($controller, $name);

    /**
     * Verify the MFA code
     *
     * @param array $data
     * @param HTTPRequest $request
     * @param string $token
     * @param ValidationResult $result
     * @return mixed
     */
    public function verifyMFA($data, $request, $token, &$result);

    /**
     * Required to find the token field for the authenticator
     *
     * @return string
     */
    public function getTokenField();
}
