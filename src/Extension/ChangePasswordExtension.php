<?php declare(strict_types=1);

namespace SilverStripe\MFA\Extension;

use Controller;
use Extension;
use LogicException;
use Security;
use Session;
use SilverStripe\MFA\JSONResponse;
use SS_Log;
use SS_HTTPRequest as HTTPRequest;
use SS_HTTPResponse as HTTPResponse;
use Injector;
use SilverStripe\MFA\Exception\InvalidMethodException;
use SilverStripe\MFA\RequestHandler\BaseHandlerTrait;
use SilverStripe\MFA\RequestHandler\VerificationHandlerTrait;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\SchemaGenerator;
use SilverStripe\MFA\Store\StoreInterface;
use Member;
use Throwable;

/**
 * Wraps the changepassword method in Security in order to allow MFA to be inserted into the flow when an AutoLoginHash
 * is being used - that is when the user has clicked a reset password link in an email after using the "forgot password"
 * functionality. When an "auto login" is not being used (a user is already logged in), it is existing functionality to
 * ask a user for their password before allowing a change - so this flow does not require MFA.
 *
 * @property Security owner
 */
class ChangePasswordExtension extends Extension
{
    use BaseHandlerTrait;
    use VerificationHandlerTrait;
    use JSONResponse;

    /**
     * Session key used to track whether multi-factor authentication has been verified during a change password
     * request flow.
     *
     * @var string
     */
    const MFA_VERIFIED_ON_CHANGE_PASSWORD = 'MultiFactorAuthenticated';

    private static $url_handlers = [
        'GET changepassword/mfa/schema' => 'getSchema', // Provides details about existing registered methods, etc.
        'GET changepassword/mfa/login/$Method' => 'startMFACheck', // Initiates login process for $Method
        'POST changepassword/mfa/login/$Method' => 'verifyMFACheck', // Verifies login via $Method
        'GET changepassword/mfa' => 'mfa', // Renders the MFA Login Page to init the app
        'changepassword' => 'handleChangePassword', // Wraps the default changepassword handler in MFA checks
    ];

    private static $allowed_actions = [
        'handleChangePassword',
        'mfa',
        'getSchema',
        'startMFACheck',
        'verifyMFACheck',
    ];

    /**
     * Supply JavaScript application configuration details, required for an MFA check
     *
     * @return HTTPResponse
     */
    public function getSchema(): HTTPResponse
    {
        try {
            $member = $this->getStore()->getMember();
            $schema = SchemaGenerator::create()->getSchema($member);
            return $this->jsonResponse(
                array_merge($schema, [
                    'endpoints' => [
                        'verify' => $this->owner->Link('mfa/login/{urlSegment}'),
                        'complete' => $this->owner->Link('changepassword'),
                    ],
                    'shouldRedirect' => false,
                ])
            );
        } catch (Throwable $exception) {
            SS_Log::log($exception->getMessage(), SS_Log::DEBUG);
            // If we don't have a valid member we shouldn't be here...
            return $this->owner->redirectBack();
        }
    }

    /**
     * Render the JavaScript app responsible for initiating an MFA check
     *
     * @return HTTPResponse|array
     */
    public function mfa()
    {
        $store = $this->getStore();
        if (!$store || !$store->getMember()) {
            return $this->owner->redirectBack();
        }

        $this->applyRequirements();

        return $this->owner->customise([
            'Form' => $this->owner->renderWith('ChangePasswordHandler'),
            'ClassName' => 'mfa',
        ])->renderWith('Security');
    }

    /**
     * Initiates the session for the user attempting to log in, in preparation for an MFA check
     *
     * @return HTTPResponse
     * @throws LogicException when no store is available
     */
    public function startMFACheck(): HTTPResponse
    {
        $request = $this->getRequest();

        $store = $this->getStore();
        if (!$store) {
            throw new LogicException('Store not found, please create one first.');
        }
        $member = $store->getMember();

        // If we don't have a valid member we shouldn't be here...
        if (!$member) {
            return $this->jsonResponse(['message' => 'Forbidden'], 403);
        }

        $method = $request->param('Method');

        if (empty($method)) {
            return $this->jsonResponse(['message' => 'Invalid request: method not present'], 400);
        }

        // Use the provided trait method for handling login
        $response = $this->createStartVerificationResponse(
            $store,
            Injector::inst()->get(MethodRegistry::class)->getMethodByURLSegment($method)
        );

        // Ensure detail is saved to the store
        $store->save($request);

        return $response;
    }

    /**
     * Checks the MFA JavaScript app input to validate the user attempting to log in
     *
     * @return HTTPResponse
     */
    public function verifyMFACheck(): HTTPResponse
    {
        $request = $this->getRequest();
        $store = $this->getStore();

        try {
            $result = $this->completeVerificationRequest($store, $request);
        } catch (InvalidMethodException $exception) {
            // Invalid method usually means a timeout. A user might be trying to verify before "starting"
            SS_Log::log($exception->getMessage(), SS_Log::DEBUG);
            return $this->jsonResponse(['message' => 'Forbidden'], 403);
        }

        if (!$result->isSuccessful()) {
            return $this->jsonResponse([
                'message' => $result->getMessage(),
            ], 401);
        }

        if (!$this->isVerificationComplete($store)) {
            return $this->jsonResponse([
                'message' => 'Additional authentication required',
            ], 202);
        }

        Session::set(self::MFA_VERIFIED_ON_CHANGE_PASSWORD, true);
        $store->clear($request);

        return $this->jsonResponse([
            'message' => 'Multi factor authenticated',
        ], 200);
    }

    public function handleChangePassword()
    {
        $session = Controller::curr()->getSession();
        $hash = $session->get('AutoLoginHash');
        /** @var Member&MemberExtension $member */
        $member = Member::member_from_autologinhash($hash);

        if ($hash
            && $member
            && $member->RegisteredMFAMethods()->exists()
            && !$session->get(self::MFA_VERIFIED_ON_CHANGE_PASSWORD)
        ) {
            Injector::inst()->create(StoreInterface::class, $member)->save($this->owner->getRequest());
            return $this->mfa();
        }

        return $this->owner->changepassword();
    }

    /**
     * Glue to support BaseHandlerTrait
     *
     * @return \NullHTTPRequest|HTTPRequest
     */
    protected function getRequest()
    {
        return $this->owner ? $this->owner->getRequest() : Controller::curr()->getRequest();
    }

    protected function extend($name, ...$data)
    {
        $this->owner->extend($name, ...$data);
    }
}
