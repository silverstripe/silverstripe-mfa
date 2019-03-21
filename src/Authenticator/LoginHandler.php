<?php
namespace SilverStripe\MFA\Authenticator;

use Exception;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\MFA\Exception\MemberNotFoundException;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Service\EnforcementManager;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Service\SchemaGenerator;
use SilverStripe\MFA\Store\SessionStore;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler as BaseLoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;
use SilverStripe\Security\Security;

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
     * A "session store" object that helps contain MFA specific session detail
     *
     * @var SessionStore
     */
    protected $sessionStore;

    /**
     * Override the parent "doLogin" to insert extra steps into the flow
     *
     * @inheritdoc
     */
    public function doLogin($data, MemberLoginForm $form, HTTPRequest $request)
    {
        $member = $this->checkLogin($data, $request, $result);

        // If there's no member it's an invalid login. We'll delegate this to the parent
        // Additionally if there are no MFA methods registered then we will also delegate
        if (!$member || !$this->getMethodRegistry()->hasMethods()) {
            return parent::doLogin($data, $form, $request);
        }

        // Store a reference to the member in session
        $this->getSessionStore()->setMember($member)->save($request);

        // Store the BackURL for use after the process is complete
        if (!empty($data)) {
            $request->getSession()->set(static::SESSION_KEY . '.additionalData', $data);
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
        if (!$this->getSessionStore()->getMember()) {
            return $this->redirectBack();
        }

        return [
            'Form' => $this->renderWith($this->getViewerTemplates()),
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
        $sessionStore = $this->getSessionStore();
        $sessionMember = $sessionStore->getMember();
        $loggedInMember = Security::getCurrentUser();

        if (is_null($loggedInMember) && is_null($sessionMember)) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.NOT_AUTHENTICATING', 'You must be logged or logging in')]],
                403
            );
        }

        // If the user isn't fully logged in and they already have a registered method, they can't register another
        if (is_null($loggedInMember) && $sessionMember && count($sessionMember->RegisteredMFAMethods()) > 0) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.MUST_USE_EXISTING_METHOD', 'This member already has an MFA method')]],
                400
            );
        }

        $method = $this->getMethodRegistry()->getMethodByURLSegment($request->param('Method'));

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
        $sessionStore->setMethod($method->getURLSegment());
        // Allow the registration handler to begin the process and generate some data to pass through to the front-end
        $data = $method->getRegisterHandler()->start($sessionStore);
        // Ensure details are saved to the session
        $sessionStore->save($request);

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
        $sessionStore = $this->getSessionStore();
        $sessionMember = $sessionStore->getMember();
        $loggedInMember = Security::getCurrentUser();

        if (is_null($loggedInMember) && is_null($sessionMember)) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.NOT_AUTHENTICATING', 'You must be logged or logging in')]],
                403
            );
        }

        $storedMethodName = $sessionStore->getMethod();
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
                    $registrationHandler->register($request, $sessionStore)
                );

            return $this->jsonResponse(['success' => true], 201);
        } catch (Exception $e) {
            return $this->jsonResponse(
                ['errors' => [
                    _t(__CLASS__ . '.REGISTER_FAILED', 'Registration failed'),
                    $e->getMessage(),
                ]],
                400
            );
        }
    }

    /**
     * Handle an HTTP request to skip MFA registration
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
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

            // Redirect the user back to wherever they originally came from when they started the login process
            return $this->redirectBack();
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
        $sessionStore = $this->getSessionStore();
        $member = $sessionStore->getMember();

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
            if (empty(!$requestedMethod)) {
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

        // Mark the given method as started within the session
        $sessionStore->setMethod($registeredMethod->getMethod()->getURLSegment());
        // Allow the authenticator to begin the process and generate some data to pass through to the front end
        $data = $registeredMethod->getLoginHandler()->start($sessionStore, $registeredMethod);
        // Ensure detail is saved to the session
        $sessionStore->save($request);

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
        $method = $this->getSessionStore()->getMethod();
        if ($method) {
            $methodInstance = $this->getMethodRegistry()->getMethodByURLSegment($method);
        }

        // We must've been to a "start" and set the method being used in session here.
        if (!$methodInstance) {
            return $this->redirectBack();
        }

        // Get the member and authenticator ready
        $member = $this->getSessionStore()->getMember();
        $registeredMethod = $this->getRegisteredMethodManager()->getFromMember($member, $methodInstance);
        $authenticator = $registeredMethod->getLoginHandler();

        if (!$authenticator->verify($request, $this->getSessionStore(), $registeredMethod)) {
            return $this->jsonResponse([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $this->addSuccessfulVerification($request, $method);

        if (!$this->isLoginComplete($request)) {
            return $this->jsonResponse([
                'message' => 'Additional authentication required',
            ], 202);
        }

        // Load the previously stored data from session and perform the login using it...
        $data = $request->getSession()->get(static::SESSION_KEY . '.additionalData');
        $this->performLogin($member, $data, $request);

        // Clear session...
        SessionStore::clear($request);
        $request->getSession()->clear(static::SESSION_KEY . '.successfulMethods');

        // Redirecting after successful login expects a getVar to be set
        if (!empty($data['BackURL'])) {
            $request->BackURL = $data['BackURL'];
        }
        return $this->jsonResponse([
            'message' => 'Access granted',
        ] + $data, 200);
    }

    public function redirectAfterSuccessfulLogin()
    {
        $request = $this->getRequest();

        // Pull "additional data" about the login from the session before clearing it
        $data = $request->getSession()->get(static::SESSION_KEY . '.additionalData');
        $request->getSession()->clear(static::SESSION_KEY . '.additionalData');

        // Redirecting after successful login expects a getVar to be set
        if (!empty($data['BackURL'])) {
            $request['BackURL'] = $data['BackURL'];
        }

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
        $member = $this->getSessionStore()->getMember() ?: Security::getCurrentUser();

        // If we don't have a valid member we shouldn't be here...
        if (!$member) {
            throw new MemberNotFoundException();
        }

        return $member;
    }

    /**
     * @return SessionStore
     */
    protected function getSessionStore()
    {
        if (!$this->sessionStore) {
            $this->sessionStore = SessionStore::create($this->getRequest());
        }

        return $this->sessionStore;
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
}
