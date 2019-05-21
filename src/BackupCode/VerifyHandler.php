<?php declare(strict_types=1);

namespace SilverStripe\MFA\BackupCode;

use RuntimeException;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\MFA\Method\Handler\VerifyHandlerInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\Notification\Service as NotificationService;
use SilverStripe\MFA\State\Result;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

class VerifyHandler implements VerifyHandlerInterface
{
    /**
     * Stores any data required to handle a login process with a method, and returns relevant state to be applied to the
     * front-end application managing the process.
     *
     * @param StoreInterface $store An object that hold session data (and the Member) that can be mutated
     * @param RegisteredMethod $method The RegisteredMethod instance that is being verified
     * @return array Props to be passed to a front-end component
     */
    public function start(StoreInterface $store, RegisteredMethod $method): array
    {
        // Provide a path to the graphic shown
        return [
            'graphic' => ModuleLoader::getModule('silverstripe/mfa')
                ->getResource('client/dist/images/recovery-codes.svg')->getURL(),
        ];
    }

    /**
     * Verify the request has provided the right information to verify the member that aligns with any sessions state
     * that may have been set prior
     *
     * @param HTTPRequest $request
     * @param StoreInterface $store
     * @param RegisteredMethod $registeredMethod The RegisteredMethod instance that is being verified
     * @return Result
     */
    public function verify(HTTPRequest $request, StoreInterface $store, RegisteredMethod $registeredMethod): Result
    {
        $bodyJSON = json_decode($request->getBody(), true);

        if (!isset($bodyJSON['code'])) {
            throw new RuntimeException(
                'Verification of backup codes requires the code to be provided but it was not given'
            );
        }

        $code = $bodyJSON['code'];

        $candidates = json_decode($registeredMethod->Data, true);

        foreach ($candidates as $index => $candidate) {
            if ($this->verifyCode($code, $candidate)) {
                // Remove the verified code from the valid list of codes
                array_splice($candidates, $index, 1);
                $registeredMethod->Data = json_encode($candidates);
                $registeredMethod->write();
                $member = $registeredMethod->Member();
                $event = CodeConsumed::create($member, ['CodesLeft' => count($candidates)]);
                NotificationService::create()->dispatchNotifications($member, $event);
                return Result::create();
            }
        }

        return Result::create(false, _t(__CLASS__ . '.INVALID_CODE', 'Invalid code'));
    }

    /**
     * Provide a localised string that serves as a lead in for choosing this option for authentication
     *
     * eg. "Enter one of your recovery codes"
     *
     * @return string
     */
    public function getLeadInLabel(): string
    {
        return _t(__CLASS__ . '.LEAD_IN', 'Verify with recovery code');
    }

    /**
     * Verifies the given code (user input) against the given hash. This uses the PHP password_hash API by default but
     * can be extended to handle a custom hash implementation
     *
     * @param string $code
     * @param string $hash
     * @return bool
     */
    protected function verifyCode($code, $hash): bool
    {
        return password_verify($code, $hash);
    }

    /**
     * Get the key that a React UI component is registered under (with @silverstripe/react-injector on the front-end)
     *
     * @return string
     */
    public function getComponent(): string
    {
        return 'BackupCodeVerify';
    }
}
