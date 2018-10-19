<?php

namespace Firesphere\BootstrapMFA\Handlers;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Forms\BootstrapMFALoginForm;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ClassLoader;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\LoginForm;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;

/**
 * Class BootstrapMFALoginHandler
 * @package Firesphere\BootstrapMFA\Handlers
 */
class BootstrapMFALoginHandler extends LoginHandler
{

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
        'MFAForm'
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
     * @param array $data
     * @param LoginForm $form
     * @param HTTPRequest $request
     * @param $validationResult
     * @return ValidationResult|Member
     * @throws ValidationException
     * @throws \SilverStripe\Security\PasswordEncryptor_NotFoundException
     */
    public function validate($data, $form, $request, &$validationResult)
    {
        if (!$validationResult) {
            $validationResult = new ValidationResult();
        }
        /** @var BootstrapMFAAuthenticator $authenticator */
        $authenticator = new BootstrapMFAAuthenticator();
        $memberID = $request->getSession()->get(BootstrapMFAAuthenticator::SESSION_KEY . '.MemberID');
        /** @var Member $member */
        $member = Member::get()->byID($memberID);

        $member = $authenticator->validateBackupCode($member, $data['token'], $validationResult);
        if ($member instanceof Member && $validationResult->isValid()) {
            return $member;
        }

        return $validationResult;
    }

    /**
     * @param array $data
     * @param MemberLoginForm $form
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function doLogin($data, MemberLoginForm $form, HTTPRequest $request)
    {
        $member = $this->checkLogin($data, $request, $message);
        // @todo temporarily, so I can log in
        if ($member->isInGracePeriod()) {
            return parent::doLogin($data, $form, $request);
        }
        $session = $request->getSession();
        /** @var Member $member */
        /** @var ValidationResult $message */
        if ($member instanceof Member && $message->isValid()) {
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
     * @return array
     */
    public function secondFactor(HTTPRequest $request)
    {
        $memberID = $request->getSession()->get(BootstrapMFAAuthenticator::SESSION_KEY . '.MemberID');
        $member = Member::get()->byID($memberID);
        $primary = $member->PrimaryMFA;
        $classManifest = ClassLoader::inst()->getManifest();
        $classNames = $classManifest->getDescendantsOf(BootstrapMFAAuthenticator::class);
        $forms = ArrayList::create();
        foreach ($classNames as $key => $className) {
            $class = Injector::inst()->get($className);
            $forms->push(['Form' => $class->getMFAForm()]);
        }

        return ['Form' => $this->MFAForm()];
    }
}
