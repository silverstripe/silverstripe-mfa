<?php
namespace SilverStripe\MFA\Authenticator;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Store\SessionStore;
use SilverStripe\Security\MemberAuthenticator\LoginHandler as BaseLoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;

class LoginHandler extends BaseLoginHandler
{
    const SESSION_KEY = 'MFALogin';

    private static $url_handlers = [
        'GET mfa/schema' => 'getSchema', // Provides details about existing registered methods, etc.
        'GET mfa/register/$Method' => 'startRegister', // Initiates registration process for $Method
        'POST mfa/register/$Method' => 'finishRegister', // Completes registration process for $Method
        'GET mfa/login/$Method' => 'startLogin', // Initiates login process for $Method
        'POST mfa/login/$Method' => 'verifyLogin', // Verifies login via $Method
        'GET mfa' => 'mfa', // Renders the MFA Login Page to init the app
    ];

    private static $allowed_actions = [
        'mfa',
        'getSchema',
        'startRegister',
        'finishRegister',
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
     *
     * @return array|HTTPResponse
     */
    public function mfa()
    {
        return [];
    }

    /**
     * Provides information about the current Member's MFA state
     *
     * @return HTTPResponse
     */
    public function getSchema()
    {
        $member = $this->getSessionStore()->getMember();

        // If we don't have a valid member we shouldn't be here...
        if (!$member) {
            return $this->redirectBack();
        }

        // Get a list of authentication for the user and the find default
        $registeredMethods = $member->RegisteredMFAMethods();

        // Pool a list of "lead in" labels. We skip the default here assuming it's not required.
        $alternateLeadInLabels = [];
        foreach ($registeredMethods as $method) {
            $alternateLeadInLabels[$method->getMethod()->getURLSegment()] =
                $method->getLoginHandler()->getLeadInLabel();
        }

        // Prepare an array to hold details for available methods to register
        $registrationDetails = [];
        $registeredMethodNames = array_keys($alternateLeadInLabels);

        // Get all methods that may be registered
        $allMethods = MethodRegistry::singleton()->getMethods();

        // Resolve details for methods that aren't setup
        foreach ($allMethods as $method) {
            // Skip registration details if the user has already registered this method
            if (in_array($method->getURLSegment(), $registeredMethodNames)) {
                continue;
            }

            $registerHandler = $method->getRegisterHandler();

            $registrationDetails[$method->getURLSegment()] = [
                'name' => $registerHandler->getName(),
                'description' => $registerHandler->getDescription(),
                'supportLink' => $registerHandler->getSupportLink(),
            ];
        }

        $defaultMethod = $member->DefaultRegisteredMethod;

        return $this->jsonResponse([
            'registeredMethods' => $alternateLeadInLabels,
            'registrationDetails' => $registrationDetails,
            'defaultMethod' => $defaultMethod ? $defaultMethod->getMethod()->getURLSegment() : null,
        ]);
    }

    /**
     * Handles the request to start a registration
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function startRegister(HTTPRequest $request)
    {
        return $this->jsonResponse(['errors' => ['Registration not yet implemented']], 500);
    }

    /**
     * Handles the request to verify and process a new registration
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function finishRegister(HTTPRequest $request)
    {
        return $this->jsonResponse(['errors' => ['Registration not yet implemented']], 500);
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
