<?php

namespace Firesphere\BootstrapMFA\Handlers;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Forms\BootstrapMFALoginForm;
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
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;
use SilverStripe\Security\Security;
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
        /** @var ValidationResult $message */
        $member = $this->checkLogin($data, $request, $message);
        // If we're in grace period, continue to the parent
        if ($member->isInGracePeriod()) {
            return parent::doLogin($data, $form, $request);
        }

        /** @var Member $member */
        if ($member instanceof Member && $message->isValid()) {
            /** @var Session $session */
            $session = $request->getSession();
            $session->set(BootstrapMFAAuthenticator::SESSION_KEY . '.MemberID', $member->ID);
            $session->set(BootstrapMFAAuthenticator::SESSION_KEY . '.Data', $data);
            if (!empty($data['BackURL'])) {
                $session->set(BootstrapMFAAuthenticator::SESSION_KEY . '.BackURL', $data['BackURL']);
            }

            return $this->redirect($this->Link('verify'));
        }

        return $this->redirectBack();
    }

    /**
     * @param HTTPRequest $request
     * @return array
     * @throws \Exception
     */
    public function secondFactor(HTTPRequest $request)
    {
        $memberID = $request->getSession()->get(BootstrapMFAAuthenticator::SESSION_KEY . '.MemberID');
        $member = Member::get()->byID($memberID);
        $primary = $member->PrimaryMFA;
        $classManifest = ClassLoader::inst()->getManifest();
        $classNames = $classManifest->getDescendantsOf(BootstrapMFAAuthenticator::class);
        $formList = [];
        foreach ($classNames as $key => $className) {
            /** @var BootstrapMFAAuthenticator $class */
            $class = Injector::inst()->get($className);
            $formList[] = $class->getMFAForm($this, static::VERIFICATION_METHOD);
        }

        $view = ArrayData::create(['Forms' => ArrayList::create($formList)]);
        $rendered = [
            'Forms'   => $formList,
            'Form'    => $view->renderWith(static::class . '_MFAForms'),
            'Primary' => $primary
        ];

        return $rendered;
    }

    /**
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function validateMFA(HTTPRequest $request)
    {
        $postVars = $request->postVars();
        /** @var BootstrapMFAAuthenticator $authenticator */
        $authenticator = Injector::inst()->get($postVars['AuthenticationMethod']);
        $field = $authenticator->getTokenField();
        /**
         * @var Member $member
         * @var ValidationResult $result
         */
        $member = $authenticator->verifyMFA($postVars, $request, $postVars[$field], $result);
        // Manually login
        if ($member && $result->isValid()) {
            Injector::inst()->get(IdentityStore::class)->logIn($member);

            return $this->redirectAfterSuccessfulLogin();
        }

        // Failure of login, trash session and redirect back
        Injector::inst()->get(IdentityStore::class)->logOut();

        return $this->redirect(Security::login_url());
    }
}
