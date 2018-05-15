<?php

namespace Firesphere\BootstrapMFA\Handlers;

use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\LoginForm;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;

abstract class BootstrapMFALoginHandler extends LoginHandler
{
    const SESSION_KEY = 'MFALogin';

    private static $url_handlers = [
        'verify' => 'secondFactor'
    ];

    private static $allowed_actions = [
        'LoginForm',
        'dologin',
        'secondFactor',
        'MFAForm'
    ];

    /**
     * @param array $data
     * @param LoginForm $form
     * @param HTTPRequest $request
     * @param $validationResult
     * @return bool|Member
     * @throws \SilverStripe\ORM\ValidationException
     * @throws \SilverStripe\Security\PasswordEncryptor_NotFoundException
     */
    public function validate($data, $form, $request, &$validationResult)
    {
        if (!$validationResult) {
            $validationResult = new ValidationResult();
        }
        /** @var BootstrapMFAProvider $provider */
        $provider = new BootstrapMFAProvider();
        $memberID = $request->getSession()->get(static::SESSION_KEY . '.MemberID');
        /** @var Member $member */
        $member = Member::get()->byID($memberID);
        $provider->setMember($member);
        $member = $provider->verifyToken($data['token'], $validationResult);
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
        $session = $request->getSession();
        $member = $this->checkLogin($data, $request, $message);
        if ($message->isValid()) {
            $session->set(static::SESSION_KEY . '.MemberID', $member->ID);
            $session->set(static::SESSION_KEY . '.Data', $data);
            if (!empty($data['BackURL'])) {
                $session->set(static::SESSION_KEY . '.BackURL', $data['BackURL']);
            }

            return $this->redirect($this->link('verify'));
        }

        return $this->redirectBack();
    }

    public function secondFactor()
    {
        return ['Form' => $this->MFAForm()];
    }

    abstract public function MFAForm();
}
