<?php declare(strict_types=1);

namespace SilverStripe\MFA\Authenticator;
use LogicException;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\RequestHandler\BaseHandlerTrait;
use SilverStripe\MFA\RequestHandler\LoginHandlerTrait;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\SchemaGenerator;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\ChangePasswordHandler as BaseChangePasswordHandler;

/**
 * Extends the "MemberAuthenticator version of the ChangePasswordHandler in order to allow MFA to be
 * inserted into the flow when an AutoLoginHash is being used  - that is when the user has clicked a
 * reset password link in an email after using the "forgot password" functionality.
 * When an "auto login" is not being used (a user is already logged in), it is existing functionality
 * to ask a user for their password before allowing a change - so this flow does not require MFA.
 */
class ChangePasswordHandler extends BaseChangePasswordHandler
{
    use BaseHandlerTrait;
    use LoginHandlerTrait;

    const MULTIFACTORAUTHENTICATED = 'MultiFactorAuthenticated';

    private static $url_handlers = [
        'GET mfa/schema' => 'getSchema', // Provides details about existing registered methods, etc.
        'GET mfa/login/$Method' => 'startMFACheck', // Initiates login process for $Method
        'POST mfa/login/$Method' => 'verifyMFACheck', // Verifies login via $Method
        'GET mfa' => 'mfa', // Renders the MFA Login Page to init the app
    ];

    private static $allowed_actions = [
        'changepassword',
        'mfa',
        'getSchema',
        'startMFACheck',
        'verifyMFACheck',
    ];

    /**
     * Respond with the given array as a JSON response
     *
     * @param array $response
     * @param int $code The HTTP response code to set on the response
     * @return HTTPResponse
     */
    protected function jsonResponse(array $response, int $code = 200): HTTPResponse
    {
        return HTTPResponse::create(json_encode($response))
            ->addHeader('Content-Type', 'application/json')
            ->setStatusCode($code);
    }

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
                        'login' => $this->Link('mfa/login/{urlSegment}'),
                        'complete' => $this->Link(),
                    ],
                    'shouldRedirect' => false,
                ])
            );
        } catch (Throwable $exception) {
            // If we don't have a valid member we shouldn't be here...
            Injector::inst()->get(LoggerInterface::class . '.mfa')->info($exception->getMessage());
            return $this->redirectBack();
        }
    }

    /**
     * Render the JavaScript app responsible for initiating an MFA check
     *
     * @return array
     */
    public function mfa(): array
    {
        $store = $this->getStore();
        if (!$store || !$store->getMember()) {
            return $this->redirectBack();
        }

        $this->applyRequirements();

        return [
            'Form' => $this->renderWith($this->getViewerTemplates()),
            'ClassName' => 'mfa',
        ];
    }

    /**
     * Initiates the session for the user attempting to log in, in preparation for an MFA check
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     * @throws LogicException when no store is available
     */
    public function startMFACheck(HTTPRequest $request): HTTPResponse
    {
        $store = $this->getStore();
        if (!$store) {
            throw new LogicException('Store not found, please create one first.');
        }
        $member = $store->getMember();

        // If we don't have a valid member we shouldn't be here...
        if (!$member) {
            return $this->jsonResponse(['message' => 'Forbidden'], 403);
        }

        // Use the provided trait method for handling login
        $response = $this->createStartLoginResponse(
            $store,
            Injector::inst()->get(MethodRegistry::class)->getMethodByURLSegment($request->param('Method'))
        );

        // Ensure detail is saved to the store
        $store->save($request);

        return $response;
    }

    /**
     * Checks the MFA JavaScript app input to validate the user attempting to log in
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function verifyMFACheck(HTTPRequest $request): HTTPResponse
    {
        $store = $this->getStore();

        try {
            $result = $this->verifyLoginRequest($store, $request);
        } catch (InvalidMethodException $exception) {
            // Invalid method usually means a timeout. A user might be trying to verify before "starting"
            Injector::inst()->get(LoggerInterface::class . '.mfa')->info($exception->getMessage());
            return $this->jsonResponse(['message' => 'Forbidden'], 403);
        }

        if (!$result->isSuccessful()) {
            return $this->jsonResponse([
                'message' => $result->getMessage(),
            ], 401);
        }

        if (!$this->isLoginComplete($store)) {
            return $this->jsonResponse([
                'message' => 'Additional authentication required',
            ], 202);
        }

        $this->getRequest()->getSession()->set(self::MULTIFACTORAUTHENTICATED, true);
        $store->clear($request);

        return $this->jsonResponse([
            'message' => 'Multi factor authenticated',
        ], 200);
    }

    public function changepassword()
    {
        $session = $this->getRequest()->getSession();
        $hash = $session->get('AutoLoginHash');
        $member = Member::member_from_autologinhash($hash);
        if ($hash
            && $member
            && $member->RegisteredMFAMethods()->exists()
            && !$session->get(self::MULTIFACTORAUTHENTICATED)
        ) {
            Injector::inst()->create(StoreInterface::class, $member)->save($this->getRequest());
            return $this->mfa();
        }
        $session->clear(self::MULTIFACTORAUTHENTICATED);
        return parent::changepassword();
    }
}
