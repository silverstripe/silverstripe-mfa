<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Authenticator;

use Psr\Log\LoggerInterface;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Exception\InvalidMethodException;
use SilverStripe\MFA\Exception\MemberNotFoundException;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\RequestHandler\BaseHandlerTrait;
use SilverStripe\MFA\RequestHandler\RegistrationHandlerTrait;
use SilverStripe\MFA\RequestHandler\VerificationHandlerTrait;
use SilverStripe\MFA\Service\EnforcementManager;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\SchemaGenerator;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler as BaseLoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;
use SilverStripe\Security\Security;

class LoginHandler extends BaseLoginHandler
{
    use BaseHandlerTrait;
    use VerificationHandlerTrait;
    use RegistrationHandlerTrait;

    public const SESSION_KEY = 'MFALogin';

    private static $url_handlers = [
        'GET mfa/schema' => 'getSchema', // Provides details about existing registered methods, etc.
        'GET mfa/register/$Method' => 'startRegistration', // Initiates registration process for $Method
        'POST mfa/register/$Method' => 'finishRegistration', // Completes registration process for $Method
        'GET mfa/skip' => 'skipRegistration', // Allows the user to skip MFA registration
        'GET mfa/verify/$Method' => 'startVerification', // Initiates verify process for $Method
        'POST mfa/verify/$Method' => 'finishVerification', // Verifies verify via $Method
        'GET mfa/complete' => 'redirectAfterSuccessfulLogin',
        'GET mfa' => 'mfa', // Renders the MFA Login Page to init the app
    ];

    private static $allowed_actions = [
        'mfa',
        'getSchema',
        'startRegistration',
        'finishRegistration',
        'skipRegistration',
        'startVerification',
        'finishVerification',
        'redirectAfterSuccessfulLogin',
    ];

    /**
     * Provide a user help link that will be available on the Introduction UI
     *
     * @config
     * @var string
     */
    // phpcs:disable
    private static $user_help_link = 'https://userhelp.silverstripe.org/en/4/optional_features/multi-factor_authentication/';
    // phpcs:enable

    /**
     * @var string[]
     */
    private static $dependencies = [
        'Logger' => '%$' . LoggerInterface::class . '.mfa',
    ];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Override the parent "doLogin" to insert extra steps into the flow
     *
     * @inheritdoc
     */
    public function doLogin($data, MemberLoginForm $form, HTTPRequest $request)
    {
        /** @var Member&MemberExtension $member */
        $member = $this->checkLogin($data, $request, $result);
        $enforcementManager = EnforcementManager::singleton();

        // If:
        //  - there's no member it's an invalid login, or
        //  - the enforcement manager determines that MFA should not be shown
        // then we can delegate to the parent as this will just be the normal login flow (without MFA)
        if (!$member || !$enforcementManager->shouldRedirectToMFA($member)) {
            return parent::doLogin($data, $form, $request);
        }

        // We need to call getSudoModeService()->activate() here otherwise the check in
        // mfa() to getSudoModeService()->check($request->getSession()) will fail
        $this->getSudoModeService()->activate($request->getSession());

        // Create a store for handling MFA for this member
        $store = $this->createStore($member);
        // We don't need to store the user's password
        $request->offsetUnset('Password');
        // User code may adjust the request properties further if they have their own sensitive data which
        // should be excluded from the store.
        $this->extend('onBeforeSaveRequestToStore', $request, $store);
        $store->save($request);

        // Store the BackURL for use after the process is complete
        if (!empty($data)) {
            $request->getSession()->set(static::SESSION_KEY . '.additionalData', $data);
        }

        // If there is at least one MFA method registered then the user MUST login with it
        $request->getSession()->clear(static::SESSION_KEY . '.mustLogin');
        if ($member->RegisteredMFAMethods()->count() > 0) {
            $request->getSession()->set(static::SESSION_KEY . '.mustLogin', true);
        } else {
            // When there are no methods then the user will be promted to register. We re-generate the session ID to
            // prevent session fixation on the MFA setup
            // NB: There's no SilverStripe API for this
            if (!headers_sent()) {
                @session_regenerate_id(true);
            }
        }

        // Redirect to the MFA step
        return $this->redirect($this->link('mfa'));
    }

    /**
     * Action handler for loading the MFA authentication React app
     * Template variables defined here will be used by the rendering controller's template - normally Page
     *
     * @return HTTPResponse|array
     */
    public function mfa(HTTPRequest $request)
    {
        $store = $this->getStore();
        if (!$store || !$store->getMember() || !$this->getSudoModeService()->check($request->getSession())) {
            return $this->redirectBack();
        }

        $this->applyRequirements();

        return [
            'Form' => $this->renderWith($this->getViewerTemplates()),
            'ClassName' => 'mfa',
        ];
    }

    /**
     * Provides information about the current Member's MFA state
     *
     * @return HTTPResponse
     */
    public function getSchema(): HTTPResponse
    {
        // Prevent caching of response
        HTTPCacheControlMiddleware::singleton()->disableCache(true);

        try {
            $member = $this->getMember();
            $schema = SchemaGenerator::create()->getSchema($member);

            return $this->jsonResponse(
                $schema + [
                    'endpoints' => [
                        'register' => $this->Link('mfa/register/{urlSegment}'),
                        'verify' => $this->Link('mfa/verify/{urlSegment}'),
                        'complete' => $this->Link('mfa/complete'),
                        'skip' => $this->Link('mfa/skip'),
                    ],
                ]
            );
        } catch (MemberNotFoundException $exception) {
            // If we don't have a valid member we shouldn't be here...
            return $this->redirectBack();
        }
    }

    /**
     * Handles the request to start a registration
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function startRegistration(HTTPRequest $request): HTTPResponse
    {
        // Prevent caching of response
        HTTPCacheControlMiddleware::singleton()->disableCache(true);

        $store = $this->getStore();
        $sessionMember = $store ? $store->getMember() : null;
        $loggedInMember = Security::getCurrentUser();

        if (
            ($loggedInMember === null && $sessionMember === null)
            || !$this->getSudoModeService()->check($request->getSession())
        ) {
            return $this->jsonResponse(
                ['errors' => [
                    _t(
                        __CLASS__ . '.NOT_AUTHENTICATING',
                        'You must be logged in or logging in. Please refresh the page and try again.'
                    )
                ]],
                403
            );
        }

        $method = $this->getMethodRegistry()->getMethodByURLSegment($request->param('Method'));

        // If the user isn't fully logged in and they already have a registered method, they can't register another
        // provided that they're not registering a backup method
        $registeredMethodCount = $sessionMember && $sessionMember->RegisteredMFAMethods()->count();
        $isRegisteringBackupMethod =
            $method instanceof MethodInterface && $this->getMethodRegistry()->isBackupMethod($method);

        if ($loggedInMember === null && $sessionMember && $registeredMethodCount > 0 && !$isRegisteringBackupMethod) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.MUST_USE_EXISTING_METHOD', 'This member already has an MFA method')]],
                400
            );
        }

        // Handle the case where the request hasn't provided an appropriate method to register
        if ($method === null) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.INVALID_METHOD', 'No such method is available')]],
                400
            );
        }

        // Ensure a store is available using the logged in member if the store doesn't exist
        if (!$store) {
            $store = $this->createStore($loggedInMember);
        }

        // Delegate to the trait for common handling
        $response = $this->createStartRegistrationResponse($store, $method);

        // Ensure details are saved to the session
        $store->save($request);

        return $response;
    }

    /**
     * Handles the request to verify and process a new registration
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function finishRegistration(HTTPRequest $request): HTTPResponse
    {
        $store = $this->getStore();
        $sessionMember = $store ? $store->getMember() : null;
        $loggedInMember = Security::getCurrentUser();

        if (
            ($loggedInMember === null && $sessionMember === null)
            || !$this->getSudoModeService()->check($request->getSession())
        ) {
            return $this->jsonResponse(
                ['errors' => [
                    _t(
                        __CLASS__ . '.NOT_AUTHENTICATING',
                        'You must be logged in or logging in. Please refresh the page and try again.'
                    )
                ]],
                403
            );
        }

        $method = $this->getMethodRegistry()->getMethodByURLSegment($request->param('Method'));
        $result = $this->completeRegistrationRequest($store, $method, $request);

        if (!$result->isSuccessful()) {
            return $this->jsonResponse(
                ['errors' => [$result->getMessage()]],
                $result->getContext()['code'] ?? 400
            );
        }

        // If we've completed registration and the member is not already logged in then we need to log them in
        $enforcementManager = EnforcementManager::create();
        $mustLogin = $request->getSession()->get(static::SESSION_KEY . '.mustLogin');

        // If the user has a valid registration at this point then we can log them in. We must ensure that they're not
        // required to log in though. The "mustLogin" flag is set at the beginning of the MFA process if they have at
        // least one method registered. They should always do that first. In that case we should assert
        // "isLoginComplete"
        if (
            (!$mustLogin || $this->isVerificationComplete($store))
            && $enforcementManager->hasCompletedRegistration($sessionMember)
        ) {
            $this->doPerformLogin($request, $sessionMember);
        }

        return $this->jsonResponse(['success' => true], 201);
    }

    /**
     * Handle an HTTP request to skip MFA registration
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     * @throws ValidationException
     */
    public function skipRegistration(HTTPRequest $request): HTTPResponse
    {
        $loginUrl = Security::login_url();

        try {
            $member = $this->getMember();
            $enforcementManager = EnforcementManager::create();

            if (!$enforcementManager->canSkipMFA($member)) {
                Security::singleton()->setSessionMessage(
                    _t(__CLASS__ . '.CANNOT_SKIP', 'You cannot skip MFA registration'),
                    ValidationResult::TYPE_ERROR
                );
                return $this->redirect($loginUrl);
            }

            $member->update(['HasSkippedMFARegistration' => true])->write();
            $this->extend('onSkipRegistration', $member);
            $this->doPerformLogin($request, $member);

            // Redirect the user back to wherever they originally came from when they started the login process
            return $this->redirectAfterSuccessfulLogin();
        } catch (MemberNotFoundException $exception) {
            Security::singleton()->setSessionMessage(
                _t(__CLASS__ . '.CANNOT_SKIP', 'You cannot skip MFA registration'),
                ValidationResult::TYPE_ERROR
            );
            return $this->redirect($loginUrl);
        }
    }

    /**
     * Handles the request to start an authentication process with an authenticator (possibly specified by the request)
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function startVerification(HTTPRequest $request): HTTPResponse
    {
        // Prevent caching of response
        HTTPCacheControlMiddleware::singleton()->disableCache(true);

        $store = $this->getStore();
        // If we don't have a valid member we shouldn't be here, or if sudo mode is not active yet.
        if (!$store || !$store->getMember() || !$this->getSudoModeService()->check($request->getSession())) {
            return $this->jsonResponse(['message' => 'Forbidden'], 403);
        }

        // Use the provided trait method for handling login
        $response = $this->createStartVerificationResponse(
            $store,
            $this->getMethodRegistry()->getMethodByURLSegment($request->param('Method'))
        );

        // Ensure detail is saved to the store
        $store->save($request);

        // Respond with our method
        return $response;
    }

    /**
     * Handles requests to authenticate from any MFA method, directing verification to the Method supplied.
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function finishVerification(HTTPRequest $request): HTTPResponse
    {
        $store = $this->getStore();
        // Enforce sudo mode
        if (!$this->getSudoModeService()->check($request->getSession())) {
            return $this->jsonResponse([
                'message' => _t(
                    __CLASS__ . '.SUDO_MODE_REQUIRED',
                    'You need to re-verify your account before continuing. Please reload and try again.'
                ),
            ], 403);
        }

        if ($store && ($member = $store->getMember()) && $member->isLockedOut()) {
            return $this->jsonResponse([
                'message' => _t(
                    __CLASS__ . '.LOCKED_OUT',
                    'Your account is temporarily locked. Please try again later.'
                ),
            ], 403);
        }

        try {
            $result = $this->completeVerificationRequest($store, $request);
        } catch (InvalidMethodException $e) {
            // Invalid method usually means a timeout. A user might be trying to verify before "starting"
            return $this->jsonResponse(['message' => 'Forbidden'], 403);
        }

        if (!$result->isSuccessful()) {
            $store->getMember()->registerFailedLogin();
            $code = $result->getContext()['code'] ?? 401;

            return $this->jsonResponse([
                'message' => $result->getMessage(),
            ], $code);
        }

        if (!$this->isVerificationComplete($store)) {
            return $this->jsonResponse([
                'message' => 'Additional authentication required',
            ], 202);
        }

        // Actually log in the member if the registration is complete
        $member = $store->getMember();

        if (EnforcementManager::create()->hasCompletedRegistration($member)) {
            $this->doPerformLogin($request, $member);

            // And also clear the session
            $store->clear($request);
        }

        // We still indicate login has been completed here. The finalisation of registration should take care of it
        return $this->jsonResponse([
            'message' => 'Login complete',
        ], 200);
    }

    public function redirectAfterSuccessfulLogin(): HTTPResponse
    {
        // Assert that we have a member logged in already. We explicitly don't use ->getMember as that will pull from
        // session during the MFA process
        $member = Security::getCurrentUser();
        $loginUrl = Security::login_url();

        if (!$member) {
            Security::singleton()->setSessionMessage(
                _t(__CLASS__ . '.MFA_LOGIN_INCOMPLETE', 'You must provide MFA login details'),
                ValidationResult::TYPE_ERROR
            );
            return $this->redirect($this->getBackURL() ?: $loginUrl);
        }

        $request = $this->getRequest();
        $enforcementManager = EnforcementManager::create();

        // Assert that the member has a valid registration.
        // This is potentially redundant logic as the member should only be logged in if they've fully registered.
        // They're allowed to login if they can skip - so only do assertions if they're not allowed to skip
        // We'll also check that they've registered the required MFA details
        if (
            !$enforcementManager->canSkipMFA($member)
            && !$enforcementManager->hasCompletedRegistration($member)
        ) {
            // Log them out again
            $identityStore = Injector::inst()->get(IdentityStore::class);
            $identityStore->logOut($request);

            Security::singleton()->setSessionMessage(
                _t(__CLASS__ . '.INVALID_REGISTRATION', 'You must complete MFA registration'),
                ValidationResult::TYPE_ERROR
            );
            return $this->redirect($this->getBackURL() ?: $loginUrl);
        }

        // Redirecting after successful login expects a getVar to be set, store it before clearing the session data
        /** @see HTTPRequest::offsetSet */
        $request['BackURL'] = $this->getBackURL();

        // Clear the "additional data"
        $request->getSession()->clear(static::SESSION_KEY . '.additionalData');

        // Ensure any left over session state is cleaned up
        $store = $this->getStore();
        if ($store) {
            $store->clear($request);
        }
        $request->getSession()->clear(static::SESSION_KEY . '.mustLogin');

        // Delegate to parent logic
        return parent::redirectAfterSuccessfulLogin();
    }

    /**
     * @return Member&MemberExtension
     * @throws MemberNotFoundException
     */
    public function getMember()
    {
        $store = $this->getStore();

        if ($store && $store->getMember()) {
            return $store->getMember();
        }

        $member = Security::getCurrentUser();

        // If we don't have a valid member we shouldn't be here...
        if (!$member) {
            throw new MemberNotFoundException();
        }

        return $member;
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger): LoginHandler
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Adds more options for the back URL - to be returned from a current MFA session store
     *
     * @return string|null
     */
    public function getBackURL(): ?string
    {
        $backURL = parent::getBackURL();

        if (!$backURL && $this->getRequest()) {
            $data = $this->getRequest()->getSession()->get(static::SESSION_KEY . '.additionalData');
            if (isset($data['BackURL'])) {
                $backURL = $data['BackURL'];
            }
        }

        return $backURL;
    }

    /**
     * Respond with the given array as a JSON response
     *
     * @param array $response
     * @param int $code The HTTP response code to set on the response
     * @return HTTPResponse
     */
    public function jsonResponse(array $response, int $code = 200): HTTPResponse
    {
        return HTTPResponse::create(json_encode($response))
            ->addHeader('Content-Type', 'application/json')
            ->setStatusCode($code);
    }

    /**
     * Complete the login process for the given member by calling "performLogin" on the parent class
     *
     * @param HTTPRequest $request
     * @param Member&MemberExtension $member
     */
    protected function doPerformLogin(HTTPRequest $request, Member $member)
    {
        // Load the previously stored data from session and perform the login using it...
        $data = $request->getSession()->get(static::SESSION_KEY . '.additionalData') ?: [];

        // Check that we don't have a logged in member before actually performing a login
        $currentMember = Security::getCurrentUser();

        if (!$currentMember) {
            // These next two lines are pulled from "parent::doLogin()"
            $this->performLogin($member, $data, $request);
            // Allow operations on the member after successful login
            parent::extend('afterLogin', $member);
        }
    }

    /**
     * @return MethodRegistry
     */
    protected function getMethodRegistry(): MethodRegistry
    {
        return MethodRegistry::singleton();
    }
}
