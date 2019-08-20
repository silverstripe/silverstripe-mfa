<?php declare(strict_types=1);

namespace SilverStripe\MFA\Authenticator;

use Member;
use MemberLoginForm;
use Security;
use Session;
use SilverStripe\MFA\Exception\InvalidMethodException;
use SilverStripe\MFA\Exception\MemberNotFoundException;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\JSONResponse;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\RequestHandler\BaseHandlerTrait;
use SilverStripe\MFA\RequestHandler\RegistrationHandlerTrait;
use SilverStripe\MFA\RequestHandler\VerificationHandlerTrait;
use SilverStripe\MFA\Service\EnforcementManager;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\SchemaGenerator;
use SS_HTTPResponse as HTTPResponse;
use ValidationException;

class LoginForm extends MemberLoginForm
{
    use BaseHandlerTrait;
    use VerificationHandlerTrait;
    use RegistrationHandlerTrait;
    use JSONResponse;

    const SESSION_KEY = 'MFALogin';

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
     * Override the parent "doLogin" to insert extra steps into the flow
     *
     * @inheritdoc
     */
    public function doLogin($data)
    {
        /** @var Member&MemberExtension $member */
        $member = call_user_func_array(array($this->authenticator_class, 'authenticate'), array($data, $this));
        $enforcementManager = EnforcementManager::singleton();

        // If:
        //  - there's no member it's an invalid login, or
        //  - the enforcement manager determines that MFA should not be shown
        // then we can delegate to the parent as this will just be the normal login flow (without MFA)
        if (!$member || !$enforcementManager->shouldRedirectToMFA($member)) {
            return parent::doLogin($data);
        }

        // Enable sudo mode. This would usually be done by the default login handler's afterLogin() hook.
        $this->getSudoModeService()->activate($this->controller->getSession());

        // Create a store for handling MFA for this member
        $store = $this->createStore($member);
        // We don't need to store the user's password
        unset($this->request['Password']);
        // User code may adjust the request properties further if they have their own sensitive data which
        // should be excluded from the store.
        $this->extend('onBeforeSaveRequestToStore', $this->request, $store);
        $store->save($this->request);

        // Store the BackURL for use after the process is complete
        if (!empty($data)) {
            Session::set(static::SESSION_KEY . '.additionalData', $data);
        }

        // If there is at least one MFA method registered then the user MUST login with it
        Session::clear(static::SESSION_KEY . '.mustLogin');
        if ($member->RegisteredMFAMethods()->count() > 0) {
            Session::set(static::SESSION_KEY . '.mustLogin', true);
        } else {
            // When there are no methods then the user will be promted to register. We re-generate the session ID to
            // prevent session fixation on the MFA setup
            // NB: There's no SilverStripe API for this
            if (!headers_sent()) {
                @session_regenerate_id(true);
            }
        }

        // Ensure session attributes are actually saved
        Session::save();

        // Redirect to the MFA step
        return $this->controller->redirect($this->makeLink('mfa'));
    }

    /**
     * Action handler for loading the MFA authentication React app
     * Template variables defined here will be used by the rendering controller's template - normally Page.ss
     *
     * @return HTTPResponse|array
     */
    public function mfa()
    {
        $store = $this->getStore();
        if (!$store || !$store->getMember() || !$this->getSudoModeService()->check($this->controller->getSession())) {
            if (!$this->getRequest()->requestVar('BackURL')) {
                return $this->controller->redirect(Security::login_url());
            }
            return $this->controller->redirectBack();
        }

        $this->applyRequirements();

        return $this->customise([
            'Form' => $this->renderWith('LoginHandler'),
            'ClassName' => 'mfa',
        ])->renderWith('Security');
    }

    /**
     * Provides information about the current Member's MFA state
     *
     * @return HTTPResponse
     */
    public function getSchema(): HTTPResponse
    {
        try {
            $member = $this->getMember();
            $schema = SchemaGenerator::create()->getSchema($member);
            return $this->jsonResponse(
                $schema + [
                    'endpoints' => [
                        'register' => $this->makeLink('mfa/register/{urlSegment}'),
                        'verify' => $this->makeLink('mfa/verify/{urlSegment}'),
                        'complete' => $this->makeLink('mfa/complete'),
                        'skip' => $this->makeLink('mfa/skip'),
                    ],
                ]
            );
        } catch (MemberNotFoundException $exception) {
            // If we don't have a valid member we shouldn't be here...
            return $this->controller->redirectBack();
        }
    }

    /**
     * Handles the request to start a registration
     *
     * @return HTTPResponse
     */
    public function startRegistration(): HTTPResponse
    {
        $request = $this->getRequest();
        $store = $this->getStore();
        $sessionMember = $store ? $store->getMember() : null;
        $loggedInMember = Member::currentUser();

        if (($loggedInMember === null && $sessionMember === null)
            || !$this->getSudoModeService()->check($this->controller->getSession())
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
     * @return HTTPResponse
     */
    public function finishRegistration(): HTTPResponse
    {
        $request = $this->getRequest();
        $store = $this->getStore();
        $sessionMember = $store ? $store->getMember() : null;
        $loggedInMember = Member::currentUser();

        if (($loggedInMember === null && $sessionMember === null)
            || !$this->getSudoModeService()->check($this->controller->getSession() ?: new Session([]))
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
        /** @var EnforcementManager $enforcementManager */
        $enforcementManager = EnforcementManager::create();
        $mustLogin = Session::get(static::SESSION_KEY . '.mustLogin');

        // If the user has a valid registration at this point then we can log them in. We must ensure that they're not
        // required to log in though. The "mustLogin" flag is set at the beginning of the MFA process if they have at
        // least one method registered. They should always do that first. In that case we should assert
        // "isLoginComplete"
        if ((!$mustLogin || $this->isVerificationComplete($store))
            && $enforcementManager->hasCompletedRegistration($sessionMember)
        ) {
            $this->doPerformLogin($sessionMember);
        }

        return $this->jsonResponse(['success' => true], 201);
    }

    /**
     * Handle an HTTP request to skip MFA registration
     *
     * @return HTTPResponse
     * @throws ValidationException
     */
    public function skipRegistration()
    {
        $loginUrl = Security::login_url();

        try {
            $member = $this->getMember();
            $enforcementManager = EnforcementManager::create();

            if (!$enforcementManager->canSkipMFA($member)) {
                return $this->controller->redirect($loginUrl);
            }

            $member->update(['HasSkippedMFARegistration' => true])->write();
            $this->extend('onSkipRegistration', $member);
            $this->doPerformLogin($member);

            // Redirect the user back to wherever they originally came from when they started the login process
            return $this->redirectAfterSuccessfulLogin();
        } catch (MemberNotFoundException $exception) {
            return $this->controller->redirect($loginUrl);
        }
    }

    /**
     * Handles the request to start an authentication process with an authenticator (possibly specified by the request)
     *
     * @return HTTPResponse
     */
    public function startVerification(): HTTPResponse
    {
        $request = $this->getRequest();
        $store = $this->getStore();
        // If we don't have a valid member we shouldn't be here, or if sudo mode is not active yet.
        if (!$store || !$store->getMember() ||
            !$this->getSudoModeService()->check($this->controller->getSession() ?: new Session([]))) {
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
     * @return HTTPResponse
     */
    public function finishVerification(): HTTPResponse
    {
        $request = $this->getRequest();
        $store = $this->getStore();
        // Enforce sudo mode
        if (!$this->getSudoModeService()->check($this->controller->getSession() ?: new Session([]))) {
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
            $this->doPerformLogin($member);

            // And also clear the session
            $store->clear($request);
        }

        // We still indicate login has been completed here. The finalisation of registration should take care of it
        return $this->jsonResponse([
            'message' => 'Login complete',
        ], 200);
    }

    public function redirectAfterSuccessfulLogin()
    {
        // Assert that we have a member logged in already. We explicitly don't use ->getMember as that will pull from
        // session during the MFA process
        $member = Member::currentUser();

        if (!$member) {
            return Security::permissionFailure(
                $this->controller,
                _t(__CLASS__ . '.MFA_LOGIN_INCOMPLETE', 'You must provide MFA login details')
            );
        }

        $request = $this->getRequest();
        /** @var EnforcementManager $enforcementManager */
        $enforcementManager = EnforcementManager::create();

        // Assert that the member has a valid registration.
        // This is potentially redundant logic as the member should only be logged in if they've fully registered.
        // They're allowed to login if they can skip - so only do assertions if they're not allowed to skip
        // We'll also check that they've registered the required MFA details
        if (!$enforcementManager->canSkipMFA($member)
            && !$enforcementManager->hasCompletedRegistration($member)
        ) {
            $member->logOut();

            return Security::permissionFailure(
                $this->controller,
                _t(__CLASS__ . '.INVALID_REGISTRATION', 'You must complete MFA registration')
            );
        }

        // Redirecting after successful login expects a getVar to be set, store it before clearing the session data
        /** @see HTTPRequest::offsetSet */
        $request['BackURL'] = $this->getBackURL();

        // Clear the "additional data"
        $data = Session::get(static::SESSION_KEY . '.additionalData');
        Session::clear(static::SESSION_KEY . '.additionalData');

        // Ensure any left over session state is cleaned up
        $store = $this->getStore();
        if ($store) {
            $store->clear($request);
        }
        Session::clear(static::SESSION_KEY . '.mustLogin');

        // Force the back url back into the request var (ugly SS3 hacks)
        if (isset($data['BackURL'])) {
            $_REQUEST['BackURL'] = $data['BackURL'];
        }

        // Delegate to parent logic
        return parent::logInUserAndRedirect($data);
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

        $member = Member::currentUser();

        // If we don't have a valid member we shouldn't be here...
        if (!$member) {
            throw new MemberNotFoundException();
        }

        return $member;
    }

    /**
     * Adds more options for the back URL - to be returned from a current MFA session store
     *
     * @return string|null
     */
    public function getBackURL(): ?string
    {
        $backURL = $_REQUEST['BackURL'] ?? $this->controller->getSession()->get('BackURL');

        if (!$backURL && $this->getRequest()) {
            $data = $this->controller->getSession()->get(static::SESSION_KEY . '.additionalData');
            if (isset($data['BackURL'])) {
                $backURL = $data['BackURL'];
            }
        }

        return $backURL;
    }

    /**
     * Complete the login process for the given member by calling "performLogin" on the parent class
     *
     * @param Member&MemberExtension $member
     */
    protected function doPerformLogin(Member $member): void
    {
        // Load the previously stored data from session and perform the login using it...
        $data = Session::get(self::SESSION_KEY . '.additionalData') ?: [];

        // Check that we don't have a logged in member before actually performing a login
        $currentMember = Member::currentUser();

        if (!$currentMember) {
            // These next two lines are pulled from "parent::doLogin()"
            $member->logIn(isset($data['Remember']));
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

    /**
     * Convenience method to generate a link to a method within this request handler.
     *
     * @param $link
     * @return string
     */
    protected function makeLink($link): string
    {
        return $this->controller->Link($this->getName() . '/' . $link);
    }
}
