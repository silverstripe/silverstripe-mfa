<?php

namespace Firesphere\BootstrapMFA\Handlers;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Extensions\MemberExtension;
use Firesphere\BootstrapMFA\Forms\BootstrapMFALoginForm;
use InvalidArgumentException;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ClassLoader;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;
use SilverStripe\Security\Security;
use SilverStripe\Security\SecurityToken;
use SilverStripe\View\ArrayData;

/**
 * Class BootstrapMFALoginHandler
 * @package Firesphere\BootstrapMFA\Handlers
 */
class BootstrapMFALoginHandler extends LoginHandler
{
    const VERIFICATION_METHOD = 'validateMFA';

    /**
     * @var array
     */
    private static $url_handlers = [
        'verify' => 'secondFactor'
    ];

    /**
     * @var array
     */
    private static $allowed_actions = [
        'LoginForm',
        'dologin',
        'secondFactor',
        'validateMFA',
    ];

    /**
     * Class names of descendants of BootstrapMFAAuthenticator
     *
     * @var string[]
     */
    protected $availableAuthenticators = [];

    /**
     * BootstrapMFALoginHandler constructor.
     * Sets up the available Authenticators
     * @param string $link
     * @param MemberAuthenticator $authenticator
     */
    public function __construct($link, MemberAuthenticator $authenticator)
    {
        $classManifest = ClassLoader::inst()->getManifest();
        $this->availableAuthenticators = $classManifest->getDescendantsOf(BootstrapMFAAuthenticator::class);

        parent::__construct($link, $authenticator);
    }

    /**
     * Return the MemberLoginForm form
     */
    public function LoginForm()
    {
        return BootstrapMFALoginForm::create(
            $this,
            get_class($this->authenticator),
            'LoginForm'
        );
    }

    /**
     * Override the doLogin method to do our own work here
     *
     * @param array $data
     * @param MemberLoginForm $form
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function doLogin($data, MemberLoginForm $form, HTTPRequest $request)
    {
        /**
         * @var ValidationResult $message
         * @var Member|MemberExtension $member
         */
        $member = $this->checkLogin($data, $request, $message);

        if (!$member) {
            return $this->redirectBack();
        }

        // If we're in grace period, continue to the parent
        if ($member->isInGracePeriod()) {
            return parent::doLogin($data, $form, $request);
        }

        if ($message->isValid()) {
            /** @var Session $session */
            $session = $request->getSession();
            $session->set(BootstrapMFAAuthenticator::SESSION_KEY . '.MemberID', $member->ID);
            $session->set(BootstrapMFAAuthenticator::SESSION_KEY . '.Data', $data);
            if (!empty($data['BackURL'])) {
                $session->set(BootstrapMFAAuthenticator::SESSION_KEY . '.BackURL', $data['BackURL']);
            }

            return $this->redirect($this->link('verify'));
        }

        return $this->redirectBack();
    }

    /**
     * Render the second factor forms for displaying at the frontend
     *
     * @param HTTPRequest $request
     * @return array
     * @throws \Exception
     */
    public function secondFactor(HTTPRequest $request)
    {
        $memberID = $request->getSession()->get(BootstrapMFAAuthenticator::SESSION_KEY . '.MemberID');
        /** @var Member|MemberExtension $member */
        $member = Member::get()->byID($memberID);

        if (!$member) {
            // Assume the session has gone state...
            return $this->redirectBack();
        }

        $primary = $member->PrimaryMFA;
        $formList = $this->getFormList();

        $view = ArrayData::create(['Forms' => ArrayList::create($formList)]);
        $rendered = [
            'Forms'   => $formList,
            'Form'    => $view->renderWith(self::class . '_MFAForms'),
            'Primary' => $primary
        ];

        $this->extend('onBeforeSecondFactor', $rendered, $view);

        return $rendered;
    }

    /**
     * Get all MFA forms from the enabled authenticators
     *
     * @return array
     */
    protected function getFormList()
    {
        $formList = [];
        foreach ($this->availableAuthenticators as $key => $className) {
            /** @var BootstrapMFAAuthenticator $class */
            $class = Injector::inst()->get($className);
            $formList[] = $class->getMFAForm($this, static::VERIFICATION_METHOD);
        }

        return $formList;
    }

    /**
     * @param HTTPRequest $request
     * @throws \InvalidArgumentException
     * @throws \Exception
     * @return HTTPResponse
     */
    public function validateMFA(HTTPRequest $request)
    {
        $postVars = $request->postVars();
        $this->validateFormData($request);

        $authenticationMethod = $postVars['AuthenticationMethod'];
        // Validate that the posted authentication method is a valid registered authenticator
        if (!$this->isValidAuthenticator($authenticationMethod)) {
            throw new InvalidArgumentException(
                sprintf('Unknown MFA authentication method "%s"', $authenticationMethod)
            );
        }

        /** @var BootstrapMFAAuthenticator $authenticator */
        $authenticator = Injector::inst()->get($authenticationMethod);
        $field = $authenticator->getTokenField();

        /** @var ValidationResult $result */
        $result = ValidationResult::create();

        /** @var Member $member */
        $member = $authenticator->verifyMFA($postVars, $request, $postVars[$field], $result);
        // Manually login
        if ($member && $result->isValid()) {
            $data = $request->getSession()->get(BootstrapMFAAuthenticator::SESSION_KEY . '.Data');
            $backURL = $request->getSession()->get('BackURL'); // defaults to null, so it's fine
            $this->performLogin($member, $data, $request);
            // Redirecting after successful login expects a getVar to be set
            $request->offsetSet('BackURL', $backURL);

            return $this->redirectAfterSuccessfulLogin();
        }

        // Failure of login, trash session and redirect back
        $this->cancelLogin($request);

        BootstrapMFALoginForm::create($this, BootstrapMFAAuthenticator::class, 'LoginForm')->sessionMessage(
            _t(
                self::class . 'MFAFAILURE',
                'Multi Factor failure'
            )
        );

        return $this->redirect(Security::login_url());
    }

    /**
     * @param HTTPRequest $request
     * @throws \Exception
     */
    protected function validateFormData(HTTPRequest $request)
    {
        /** @var SecurityToken $securityToken */
        $securityToken = Injector::inst()->get(SecurityToken::class);
        $tokenCheck = $securityToken->check($request->postVar('SecurityID'));

        $authenticationMethod = $request->postVar('AuthenticationMethod');
        if (!$tokenCheck || !$this->isValidAuthenticator($authenticationMethod)) {
            // Failure of login, trash session and redirect back
            $this->cancelLogin($request);
            // User tampered with the authentication method input. Thus invalidate
            throw new \Exception('Invalid authentication', 1);
        }
    }



    /**
     * @param $authenticationMethod
     * @return bool
     */
    protected function isValidAuthenticator($authenticationMethod)
    {
        return in_array($authenticationMethod, $this->availableAuthenticators, true);
    }

    /**
     * @param HTTPRequest $request
     */
    protected function cancelLogin(HTTPRequest $request)
    {
        $request->getSession()->clear(BootstrapMFAAuthenticator::SESSION_KEY);
        Injector::inst()->get(IdentityStore::class)->logOut();
    }
}
