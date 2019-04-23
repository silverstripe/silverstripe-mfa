<?php
namespace SilverStripe\MFA\Authenticator;

use Exception;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Exception\MemberNotFoundException;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Service\EnforcementManager;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Service\SchemaGenerator;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler as BaseLoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;

class LoginHandler extends BaseLoginHandler
{
    const SESSION_KEY = 'MFALogin';

    private static $url_handlers = [
        'GET mfa/schema' => 'getSchema', // Provides details about existing registered methods, etc.
        'GET mfa/register/$Method' => 'startRegistration', // Initiates registration process for $Method
        'POST mfa/register/$Method' => 'finishRegistration', // Completes registration process for $Method
        'GET mfa/skip' => 'skipRegistration', // Allows the user to skip MFA registration
        'GET mfa/login/$Method' => 'startLogin', // Initiates login process for $Method
        'POST mfa/login/$Method' => 'verifyLogin', // Verifies login via $Method
        'GET mfa/complete' => 'redirectAfterSuccessfulLogin',
        'GET mfa' => 'mfa', // Renders the MFA Login Page to init the app
    ];

    private static $allowed_actions = [
        'mfa',
        'getSchema',
        'startRegistration',
        'finishRegistration',
        'skipRegistration',
        'startLogin',
        'verifyLogin',
        'redirectAfterSuccessfulLogin',
    ];

    /**
     * Indicate how many MFA methods the user must authenticate with before they are considered logged in
     *
     * @config
     * @var int
     */
    private static $required_mfa_methods = 1;

    /**
     * Provide a user help link that will be available on the Introduction UI
     *
     * @config
     * @var string
     */
    private static $user_help_link = 'nope';

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
     * A "session store" object that helps contain MFA specific session detail
     *
     * @var StoreInterface
     */
    protected $store;

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

        // If there's no member it's an invalid login. We'll delegate this to the parent
        // Additionally if there are no MFA methods registered then we will also delegate
        if (!$member || !$this->getMethodRegistry()->hasMethods()) {
            return parent::doLogin($data, $form, $request);
        }

        // Store a reference to the member in session
        $this->getStore()->setMember($member)->save($request);

        // Store the BackURL for use after the process is complete
        if (!empty($data)) {
            $request->getSession()->set(static::SESSION_KEY . '.additionalData', $data);
        }

        // If there is at least one MFA method registered then the user MUST login with it
        $request->getSession()->clear(static::SESSION_KEY . '.mustLogin');
        if ($member->RegisteredMFAMethods()->count() > 0) {
            $request->getSession()->set(static::SESSION_KEY . '.mustLogin', true);
        }

        // Bypass the MFA UI if the user can and has skipped it or MFA is not enabled
        if (!$enforcementManager->shouldRedirectToMFA($member)) {
            $this->doPerformLogin($request, $member);
            return $this->redirectAfterSuccessfulLogin();
        }

        // Redirect to the MFA step
        return $this->redirect($this->link('mfa'));
    }

    /**
     * Action handler for loading the MFA authentication React app
     * Template variables defined here will be used by the rendering controller's template - normally Page.ss
     *
     * @return array|HTTPResponse template variables {@see SilverStripe\Security\Security::renderWrappedController}
     */
    public function mfa()
    {
        if (!$this->getStore()->getMember()) {
            return $this->redirectBack();
        }

        // Run through requirements
        Requirements::javascript('silverstripe/mfa: client/dist/js/injector.js');
        Requirements::javascript('silverstripe/admin: client/dist/js/i18n.js');
        Requirements::javascript('silverstripe/mfa: client/dist/js/bundle.js');
        Requirements::add_i18n_javascript('silverstripe/mfa: client/lang');
        Requirements::css('silverstripe/mfa: client/dist/styles/bundle.css');

        // Plugin module requirements
        $registry = $this->getMethodRegistry();
        foreach ($registry->getMethods() as $method) {
            $method->applyRequirements();
        }

        return [
            'Form' => $this->renderWith($this->getViewerTemplates()),
            'ClassName' => 'mfa',
        ];
    }

    /**
     * Provides information about the current Member's MFA state
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function getSchema(HTTPRequest $request)
    {
        try {
            $member = $this->getMember();
            $schema = SchemaGenerator::create()->getSchema($member);
            return $this->jsonResponse(
                $schema + [
                    'endpoints' => [
                        'register' => $this->Link('mfa/register/{urlSegment}'),
                        'login' => $this->Link('mfa/login/{urlSegment}'),
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
    public function startRegistration(HTTPRequest $request)
    {
        $store = $this->getStore();
        $sessionMember = $store->getMember();
        $loggedInMember = Security::getCurrentUser();

        if (is_null($loggedInMember) && is_null($sessionMember)) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.NOT_AUTHENTICATING', 'You must be logged or logging in')]],
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

        if ($method === null) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.INVALID_METHOD', 'No such method is available')]],
                400
            );
        }

        // Fall back to the logged in user at this point if not in the login process
        $member = $sessionMember ?: $loggedInMember;

        // Sanity check that the method hasn't already been registered
        $existingRegisteredMethod = $this->getRegisteredMethodManager()->getFromMember($member, $method);

        if ($existingRegisteredMethod) {
            return $this->jsonResponse(
                [
                    'errors' => [
                        _t(
                            __CLASS__ . '.METHOD_ALREADY_REGISTERED',
                            'That method has already been registered against this Member'
                        )
                    ]
                ],
                400
            );
        }

        // Mark the given method as started within the session
        $store->setMethod($method->getURLSegment());
        // Allow the registration handler to begin the process and generate some data to pass through to the front-end
        $data = $method->getRegisterHandler()->start($store);
        // Ensure details are saved to the session
        $store->save($request);

        return $this->jsonResponse($data);
    }

    /**
     * Handles the request to verify and process a new registration
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function finishRegistration(HTTPRequest $request)
    {
        $store = $this->getStore();
        $sessionMember = $store->getMember();
        $loggedInMember = Security::getCurrentUser();

        if (is_null($loggedInMember) && is_null($sessionMember)) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.NOT_AUTHENTICATING', 'You must be logged or logging in')]],
                403
            );
        }

        $storedMethodName = $store->getMethod();
        $method = $this->getMethodRegistry()->getMethodByURLSegment($request->param('Method'));

        // If a registration process hasn't been initiated in a previous request, calling this method is invalid
        if (!$storedMethodName) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.NO_REGISTRATION_IN_PROGRESS', 'No registration in progress')]],
                400
            );
        }

        if ($storedMethodName !== $method->getURLSegment()) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.METHOD_MISMATCH', 'Method does not match registration in progress')]],
                400
            );
        }

        $registrationHandler = $method->getRegisterHandler();

        try {
            $this->getRegisteredMethodManager()
                ->registerForMember(
                    $sessionMember,
                    $method,
                    $registrationHandler->register($request, $store)
                );
        } catch (Exception $e) {
            $this->getLogger()->debug('MFA registration failed: ' . $e->getMessage(), $e->getTrace());
            $this->extend('onRegisterMethodFailure', $sessionMember, $method);

            return $this->jsonResponse(
                ['errors' => [$e->getMessage()]],
                400
            );
        }

        // If we've completed registration and the member is not already logged in then we need to log them in
        /** @var EnforcementManager $enforcementManager */
        $enforcementManager = EnforcementManager::create();
        $mustLogin = $request->getSession()->get(static::SESSION_KEY . '.mustLogin');

        // If the user has a valid registration at this point then we can log them in. We must ensure that they're not
        // required to log in though. The "mustLogin" flag is set at the beginning of the MFA process if they have at
        // least one method registered. They should always do that first. In that case we should assert
        // "isLoginComplete"
        if ((!$mustLogin || $this->isLoginComplete($request))
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
    public function skipRegistration(HTTPRequest $request)
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
    public function startLogin(HTTPRequest $request)
    {
        $store = $this->getStore();
        $member = $store->getMember();

        // If we don't have a valid member we shouldn't be here...
        if (!$member) {
            return $this->redirectBack();
        }

        // Pull a method to use from the request...
        $requestedMethod = $this->getMethodRegistry()->getMethodByURLSegment($request->param('Method'));
        $registeredMethod = null;
        if ($requestedMethod) {
            $registeredMethod = $this->getRegisteredMethodManager()->getFromMember($member, $requestedMethod);
        }

        // ...Or use the default (TODO: Should we have the default as a fallback? Maybe just if no method is specified?)
        if (!$registeredMethod) {
            $registeredMethod = $member->DefaultRegisteredMethod;
        }

        // We can't proceed with login if the Member doesn't have this method registered
        if (!$registeredMethod) {
            // We can display a specific message if there was no method specified
            if (!$requestedMethod) {
                $message = _t(
                    __CLASS__ . '.METHOD_NOT_PROVIDED',
                    'No method was provided to login with and the Member has no default'
                );
            } else {
                $message = _t(__CLASS__ . '.METHOD_NOT_REGISTERED', 'Member does not have this method registered');
            }

            $this->jsonResponse(
                ['errors' => [$message]],
                400
            );
        }

        // Mark the given method as started within the store
        $store->setMethod($registeredMethod->getMethod()->getURLSegment());
        // Allow the authenticator to begin the process and generate some data to pass through to the front end
        $data = $registeredMethod->getLoginHandler()->start($store, $registeredMethod);
        // Ensure detail is saved to the store
        $store->save($request);

        // Respond with our method
        return $this->jsonResponse($data ?: []);
    }

    /**
     * Handles requests to authenticate from any MFA method, directing verification to the Method supplied.
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function verifyLogin(HTTPRequest $request)
    {
        $method = $this->getStore()->getMethod();
        if ($method) {
            $methodInstance = $this->getMethodRegistry()->getMethodByURLSegment($method);
        }

        // We must've been to a "start" and set the method being used in session here.
        if (!$methodInstance) {
            return $this->redirectBack();
        }

        // Get the member and authenticator ready
        $member = $this->getStore()->getMember();
        $registeredMethod = $this->getRegisteredMethodManager()->getFromMember($member, $methodInstance);
        $authenticator = $registeredMethod->getLoginHandler();

        if (!$authenticator->verify($request, $this->getStore(), $registeredMethod)) {
            $member->registerFailedLogin();
            $this->extend('onMethodVerificationFailure', $member, $methodInstance);

            return $this->jsonResponse([
                'message' => 'Invalid credentials',
            ], 401);
        }
        $this->extend('onMethodVerificationSuccess', $member, $methodInstance);

        $this->addSuccessfulVerification($request, $method);

        if (!$this->isLoginComplete($request)) {
            return $this->jsonResponse([
                'message' => 'Additional authentication required',
            ], 202);
        }

        // Actually log in the member if the registration is complete
        $enforcementManager = EnforcementManager::create();
        if ($enforcementManager->hasCompletedRegistration($member)) {
            $this->doPerformLogin($request, $member);

            // And also clear the session
            $this->getStore()->clear($request);
            $request->getSession()->clear(static::SESSION_KEY . '.successfulMethods');
        }

        return $this->jsonResponse([
            'message' => 'Login complete',
        ], 200);
    }

    public function redirectAfterSuccessfulLogin()
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
            return $this->redirect($loginUrl);
        }

        $request = $this->getRequest();
        /** @var EnforcementManager $enforcementManager */
        $enforcementManager = EnforcementManager::create();

        // Assert that the member has a valid registration.
        // This is potentially redundant logic as the member should only be logged in if they've fully registered.
        // They're allowed to login if they can skip - so only do assertions if they're not allowed to skip
        // We'll also check that they've registered the required MFA details
        if (!$enforcementManager->canSkipMFA($member) && !$enforcementManager->hasCompletedRegistration($member)) {
            // Log them out again
            /** @var IdentityStore $identityStore */
            $identityStore = Injector::inst()->get(IdentityStore::class);
            $identityStore->logOut($request);

            Security::singleton()->setSessionMessage(
                _t(__CLASS__ . '.INVALID_REGISTRATION', 'You must complete MFA registration'),
                ValidationResult::TYPE_ERROR
            );
            return $this->redirect($loginUrl);
        }

        // Clear the "additional data"
        $data = $request->getSession()->get(static::SESSION_KEY . '.additionalData') ?: [];
        $request->getSession()->clear(static::SESSION_KEY . '.additionalData');

        // Redirecting after successful login expects a getVar to be set
        if (!empty($data['BackURL'])) {
            $request['BackURL'] = $data['BackURL'];
        }

        // Ensure any left over session state is cleaned up
        $this->getStore()->clear($request);
        $request->getSession()->clear(static::SESSION_KEY . '.mustLogin');
        $request->getSession()->clear(static::SESSION_KEY . '.successfulMethods');

        // Delegate to parent logic
        return parent::redirectAfterSuccessfulLogin();
    }

    /**
     * Respond with the given array as a JSON response
     *
     * @param array $response
     * @return HTTPResponse
     */
    protected function jsonResponse(array $response, $code = 200)
    {
        return HTTPResponse::create(json_encode($response))
            ->addHeader('Content-Type', 'application/json')
            ->setStatusCode($code);
    }

    /**
     * Indicate that the user has successfully verified the given authentication method
     *
     * @param HTTPRequest $request
     * @param string $method The method class name
     * @return LoginHandler
     */
    protected function addSuccessfulVerification(HTTPRequest $request, $method)
    {
        // Pull the prior sucesses from the session
        $key = static::SESSION_KEY . '.successfulMethods';
        $successfulMethods = $request->getSession()->get($key);

        // Coalesce these methods
        if (!$successfulMethods) {
            $successfulMethods = [];
        }

        // Add our new success
        $successfulMethods[] = $method;

        // Ensure it's persisted in session
        $request->getSession()->set($key, $successfulMethods);

        return $this;
    }

    protected function isLoginComplete(HTTPRequest $request)
    {
        // Pull the successful methods from session
        $successfulMethods = $request->getSession()->get(static::SESSION_KEY . '.successfulMethods');

        // Zero is "not complete". There's different config for optional MFA
        if (!is_array($successfulMethods) || !count($successfulMethods)) {
            return false;
        }

        return count($successfulMethods) >= static::config()->get('required_mfa_methods');
    }

    /**
     * @return Member&MemberExtension
     * @throws MemberNotFoundException
     */
    public function getMember()
    {
        $member = $this->getStore()->getMember() ?: Security::getCurrentUser();

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
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return StoreInterface
     */
    protected function getStore()
    {
        if (!$this->store) {
            $this->store = Injector::inst()->create(StoreInterface::class, $this->getRequest());
        }

        return $this->store;
    }

    /**
     * @return MethodRegistry
     */
    protected function getMethodRegistry()
    {
        return MethodRegistry::singleton();
    }

    /**
     * @return RegisteredMethodManager
     */
    protected function getRegisteredMethodManager()
    {
        return RegisteredMethodManager::singleton();
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
            $this->extend('onLoginSuccess', $member);
        }
    }
}
