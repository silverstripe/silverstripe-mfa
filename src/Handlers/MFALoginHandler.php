<?php

namespace Firesphere\BootstrapMFA\Handlers;

use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\LoginForm;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\LoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;

abstract class MFALoginHandler extends LoginHandler
{
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
     * @return bool|Member
     */
    public function validate($data, $form, $request)
    {
        /** @var BootstrapMFAProvider $provider */
        $provider = new BootstrapMFAProvider();
        $memberID = $request->getSession()->get('MFALogin.MemberID');
        /** @var Member $member */
        $member = Member::get()->byID($memberID);
        $provider->setMember($member);
        $member = $provider->verifyToken($data['token'], $result);
        if ($result->isValid()) {
            return $member;
        }
        return false;
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
            $session->set('MFALogin.MemberID', $member->ID);
            $session->set('MFALogin.Data', $data);
            if (!empty($data['BackURL'])) {
                $session->set('MFALogin.BackURL', $data['BackURL']);
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
