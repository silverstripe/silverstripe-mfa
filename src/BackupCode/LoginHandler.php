<?php

namespace SilverStripe\MFA\BackupCode;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\MFA\Method\Handler\LoginHandlerInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Store\StoreInterface;

class LoginHandler implements LoginHandlerInterface
{
    /**
     * Stores any data required to handle a login process with a method, and returns relevant state to be applied to the
     * front-end application managing the process.
     *
     * @param StoreInterface $store An object that hold session data (and the Member) that can be mutated
     * @param RegisteredMethod $method The RegisteredMethod instance that is being verified
     * @return array Props to be passed to a front-end component
     */
    public function start(StoreInterface $store, RegisteredMethod $method)
    {
        // No-op
    }

    /**
     * Verify the request has provided the right information to verify the member that aligns with any sessions state
     * that may have been set prior
     *
     * @param HTTPRequest $request
     * @param StoreInterface $store
     * @param RegisteredMethod $registeredMethod The RegisteredMethod instance that is being verified
     * @return bool
     */
    public function verify(HTTPRequest $request, StoreInterface $store, RegisteredMethod $registeredMethod)
    {
        $bodyJSON = json_decode($request->getBody(), true);
        $code = $bodyJSON['code'];

        $candidates = json_decode($registeredMethod->Data, true);

        foreach ($candidates as $index => $candidate) {
            if ($this->verifyCode($code, $candidate)) {
                // Remove the verified code from the valid list of codes
                array_splice($candidates, $index, 1);
                $registeredMethod->Data = json_encode($candidates);
                $registeredMethod->write();
                return true;
            }
        }

        return false;
    }

    /**
     * Provide a localised string that serves as a lead in for choosing this option for authentication
     *
     * eg. "Enter one of your recovery codes"
     *
     * @return string
     */
    public function getLeadInLabel()
    {
        return _t(__CLASS__ . '.LEAD_IN', 'Enter one of your recovery codes');
    }

    /**
     * Verifies the given code (user input) against the given hash. This uses the PHP password_hash API by default but
     * can be extended to handle a custom hash implementation
     *
     * @param string $code
     * @param string $hash
     * @return bool
     */
    protected function verifyCode($code, $hash)
    {
        return password_verify($code, $hash);
    }

    /**
     * Get the key that a React UI component is registered under (with @silverstripe/react-injector on the front-end)
     *
     * @return string
     */
    public function getComponent()
    {
        return 'BackupCodeLogin';
    }
}
