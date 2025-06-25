<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Extension\AccountReset;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\MFA\RequestHandler\BaseHandlerTrait;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Forms\ConfirmedPasswordField;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Extends the Security controller to support Account Resets. This extension can
 * itself be extended to add procedures to the reset action (such as removing
 * additional authentication factors, sending alerts, etc.)
 *
 * @extends Extension<Security>
 */
class SecurityExtension extends Extension
{
    use BaseHandlerTrait;
    use Extensible;

    private static $url_handlers = [
        'GET reset-account' => 'resetaccount',
    ];

    private static $allowed_actions = [
        'resetaccount',
        'ResetAccountForm',
    ];

    public function resetaccount(HTTPRequest $request)
    {
        if (Security::getCurrentUser()) {
            $output = $this->owner->renderWith(
                'Security',
                [
                    'Title' => _t(
                        __CLASS__ . '.ALREADYAUTHENTICATEDTITLE',
                        'Already authenticated'
                    ),
                    'Content' => _t(
                        __CLASS__ . '.ALREADYAUTHENTICATEDBODY',
                        'You must be logged out to reset your account.'
                    ),
                ]
            );
            return $this->owner->getResponse()->setBody($output)->setStatusCode(400);
        }

        $vars = $request->getVars();

        /** @var Member|MemberExtension $member */
        $member = Member::get()->byID(intval($vars['m'] ?? 0));

        if (is_null($member) || $member->verifyAccountResetToken($vars['t'] ?? '') === false) {
            $output = $this->owner->renderWith(
                'Security',
                [
                    'Title' => _t(
                        __CLASS__ . '.INVALIDTOKENTITLE',
                        'Invalid member or token'
                    ),
                    'Content' => _t(
                        __CLASS__ . '.INVALIDTOKENBODY',
                        'Your account reset token may have expired. Please contact an administrator.'
                    )
                ]
            );
            return $this->owner->getResponse()->setBody($output)->setStatusCode(400);
        }

        $request->getSession()->set('MemberID', $member->ID);

        return $this->owner->getResponse()->setBody($this->owner->renderWith(
            'Security',
            [
                'Title' => _t(
                    __CLASS__ . '.ACCOUNT_RESET_TITLE',
                    'Reset account'
                ),
                'Message' => _t(
                    __CLASS__ . '.ACCOUNT_RESET_DESCRIPTION',
                    'Your password will be changed, and any registered MFA methods will be removed.'
                ),
                'Form' => $this->ResetAccountForm(),
            ]
        ));
    }

    public function ResetAccountForm(): Form
    {
        $field = ConfirmedPasswordField::create(
            'Password',
            _t(Member::class . '.NEWPASSWORD', 'New Password'),
            '',
            null,
            false,
            _t(Member::class . '.CONFIRMNEWPASSWORD', 'Confirm New Password')
        );
        $field->setIsOnMemberForm(true);
        $fields = FieldList::create([
            $field,
        ]);
        $actions = FieldList::create([
            FormAction::create('doResetAccount', 'Reset account'),
        ]);
        $form = Form::create($this->owner, 'ResetAccountForm', $fields, $actions);
        $this->owner->extend('updateResetAccountForm', $form);
        return $form;
    }

    /**
     * Resets the user's password, and triggers other account reset procedures
     *
     * @param array $data
     * @param Form $form
     * @return HTTPResponse
     */
    public function doResetAccount(array $data, Form $form): HTTPResponse
    {
        $memberID = $this->owner->getRequest()->getSession()->get('MemberID');

        // If the ID isn't in the session, politely assume the session has expired
        if (!$memberID) {
            $form->sessionMessage(
                _t(
                    __CLASS__ . '.RESETTIMEDOUT',
                    "The account reset process timed out. Please click the link in the email and try again."
                ),
                ValidationResult::TYPE_ERROR
            );

            return $this->owner->redirectBack();
        }

        /** @var Member&MemberExtension $member */
        $member = Member::get()->byID((int) $memberID);
        $password = $data['Password']['_Password'] ?? null;

        // Check if the new password is accepted
        $validationResult = $member->changePassword($password);
        if (!$validationResult->isValid()) {
            $form->setSessionValidationResult($validationResult);

            return $this->owner->redirectBack();
        }

        // Clear locked out status
        $member->LockedOutUntil = null;
        $member->FailedLoginCount = null;

        // Clear account reset data
        $member->AccountResetHash = null;
        $member->AccountResetExpired = DBDatetime::create()->now();
        $member->write();

        // Pass off to extensions to perform any additional reset actions
        $this->extend('handleAccountReset', $member);

        // Send the user along to the login form (allowing any additional factors to kick in as needed)
        $this->owner->setSessionMessage(
            _t(
                __CLASS__ . '.RESETSUCCESSMESSAGE',
                'Reset complete. Please log in with your new password.'
            ),
            ValidationResult::TYPE_GOOD
        );
        return $this->owner->redirect($this->owner->Link('login'));
    }
}
