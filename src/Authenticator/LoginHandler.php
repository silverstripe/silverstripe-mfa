<?php
namespace SilverStripe\MFA\Authenticator;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Store\SessionStore;
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
        'GET mfa/login/$Method' => 'startLogin', // Initiates login process for $Method
        'POST mfa/login/$Method' => 'verifyLogin', // Verifies login via $Method
        'GET mfa' => 'mfa', // Renders the MFA Login Page to init the app
    ];

    private static $allowed_actions = [
        'mfa',
        'getSchema',
        'startRegistration',
        'finishRegistration',
        'startLogin',
        'verifyLogin',
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
     * @return array template variables {@see SilverStripe\Security\Security::renderWrappedController}
     */
    public function mfa()
    {
        return [
            'Form' => $this->renderWith($this->getViewerTemplates()),
        ];
    }

    /**
     * Provides information about the current Member's MFA state
     *
     * @return HTTPResponse
     */
    public function getSchema()
    {
        $member = $this->getSessionStore()->getMember();

        if (!$member) {
            $member = Security::getCurrentUser();
        }

        // If we don't have a valid member we shouldn't be here...
        if (!$member) {
            return $this->redirectBack();
        }

        // Get a list of methods registered to the user
        $registeredMethods = $member->RegisteredMFAMethods();

        // Generate a map of URL Segments to 'lead in labels', which are used to describe the method in the login UI
        $registeredMethodDetails = [];
        foreach ($registeredMethods as $registeredMethod) {
            $method = $registeredMethod->getMethod();
            $registeredMethodDetails[] = [
                'urlSegment' => $method->getURLSegment(),
                'leadInLabel' => $method->getLoginHandler()->getLeadInLabel()
            ];
        }

        // Prepare an array to hold details for methods available to register
        $availableMethodDetails = [];
        $registeredMethodNames = array_keys($registeredMethodDetails);

        // Get all methods enabled on the site
        $allMethods = MethodRegistry::singleton()->getMethods();

        // Compile details for methods that aren't already registered to the user
        foreach ($allMethods as $method) {
            // Skip registration details if the user has already registered this method
            if (in_array($method->getURLSegment(), $registeredMethodNames)) {
                continue;
            }

            $registerHandler = $method->getRegisterHandler();

            $availableMethodDetails[] = [
                'urlSegment' => $method->getURLSegment(),
                'name' => $registerHandler->getName(),
                'description' => $registerHandler->getDescription(),
                'supportLink' => $registerHandler->getSupportLink(),
            ];
        }

        $defaultMethod = $member->DefaultRegisteredMethod;

        return $this->jsonResponse([
            'registeredMethods' => $registeredMethodDetails,
            'availableMethods' => $availableMethodDetails,
            'defaultMethod' => $defaultMethod ? $defaultMethod->getMethod()->getURLSegment() : null,
        ]);
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

        if (is_null($method)) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.INVALID_METHOD', 'No such method is available')]],
                400
            );
        }

        // Fall back to the logged in user at this point if not in the login process
        $member = $sessionMember ?: $loggedInMember;

        // Sanity check that the method hasn't already been registered
        $existingRegisteredMethod = $this->getMethodRegistry()->getMethodFromMember($member, get_class($method));

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
        $sessionStore->setMethod(get_class($method));
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

        $success = $registrationHandler->register($request, $sessionStore);

        if ($success) {
            // TODO: Do we need to send any further details back here?
            return $this->jsonResponse(['success' => true], 201);
        }

        return $this->jsonResponse(
            ['errors' => [_t(__CLASS__ . '.REGISTER_FAILED', 'Registration failed')]],
            400
        );
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

        // Pull a method to use from the request or use the default (TODO: Should we have the default as a fallback?)
        $specifiedMethod = str_replace('-', '\\', $request->param('Method')) ?: $member->DefaultRegisteredMethod;
        $method = $this->getMethodRegistry()->getMethodFromMember($member, $specifiedMethod);

        // We can't proceed with login if the Member doesn't have this method registered
        if (!$method) {
            $this->jsonResponse(
                ['errors' => [
                    _t(__CLASS__ . '.METHOD_NOT_REGISTERED', 'Member does not have this method registered')
                ]],
                400
            );
        }

        // Mark the given method as started within the session
        $sessionStore->setMethod($method->MethodClassName);
        // Allow the authenticator to begin the process and generate some data to pass through to the front end
        $data = $method->getLoginHandler()->start($sessionStore);
        // Ensure detail is saved to the session
        $sessionStore->save($request);

        // Respond with our method
        return $this->jsonResponse($data);
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

        // We must've been to a "start" and set the method being used in session here.
        if (!$method) {
            return $this->redirectBack();
        }

        // Get the member and authenticator ready
        $member = $this->getSessionStore()->getMember();
        $authenticator = $this->getMethodRegistry()->getMethodFromMember($member, $method)->getLoginHandler();

        if (!$authenticator->verify($request, $this->getSessionStore())) {
            // TODO figure out how to return a message here too.
            return $this->redirect($this->link('mfa'));
        }

        $this->addSuccessfulVerification($request, $method);

        if (!$this->isLoginComplete($request)) {
            return $this->redirect($this->link('mfa'));
        }

        // Load the previously stored data from session and perform the login using it...
        $data = $request->getSession()->get(static::SESSION_KEY . '.additionalData');
        $this->performLogin($member, $data, $request);

        // Clear session...
        SessionStore::clear($request);
        $request->getSession()->clear(static::SESSION_KEY . '.additionalData');
        $request->getSession()->clear(static::SESSION_KEY . '.successfulMethods');

        // Redirecting after successful login expects a getVar to be set
        if (!empty($data['BackURL'])) {
            $request->BackURL = $data['BackURL'];
        }
        return $this->redirectAfterSuccessfulLogin();
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
     * Helper method for getting an instance of a method registry
     *
     * @return MethodRegistry
     */
    protected function getMethodRegistry()
    {
        return MethodRegistry::singleton();
    }
}
