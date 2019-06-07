<?php declare(strict_types=1);

namespace SilverStripe\MFA\Extension\AccountReset;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\PasswordField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\MFA\JSONResponse;
use SilverStripe\MFA\RequestHandler\BaseHandlerTrait;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Extends the Security controller to support Account Resets. This extension can
 * itself be extended to add procedures to the reset action (such as removing
 * additional authentication factors, sending alerts, etc.)
 *
 * @package SilverStripe\MFA\Extension
 * @property Security owner
 */
class SecurityExtension extends Extension
{
    use JSONResponse, BaseHandlerTrait, Extensible;

    private static $url_handlers = [
        'GET reset-account' => 'resetaccount',
        'ResetAccountForm' => 'ResetAccountForm',
    ];

    private static $allowed_actions = [
        'resetaccount',
        'ResetAccountForm',
    ];

    public function resetaccount(HTTPRequest $request): HTTPResponse
    {
        if (Security::getCurrentUser()) {
            return $this->jsonResponse(['error' => 'Already authenticated'], 400);
        }

        ['m' => $memberID, 't' => $token] = $request->getVars();

        /** @var Member|MemberExtension $member */
        $member = Member::get()->byID($memberID);

        if (is_null($member) || $member->verifyAccountResetToken($token) === false) {
            return $this->jsonResponse(['error' => 'Invalid member or token'], 400);
        }

        $request->getSession()->set('MemberID', $member->ID);

        return $this->owner->getResponse()->setBody($this->owner->renderWith(
            'Security',
            [
                'Title' => _t(
                    __CLASS__ . '.ACCOUNT_RESET_TITLE',
                    'Reset Account'
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
        $fields = FieldList::create([
            PasswordField::create(
                'NewPassword1',
                _t(
                    'SilverStripe\\Security\\Member.NEWPASSWORD',
                    'New Password'
                )
            ),
            PasswordField::create(
                'NewPassword2',
                _t(
                    'SilverStripe\\Security\\Member.CONFIRMNEWPASSWORD',
                    'Confirm New Password'
                )
            ),
        ]);

        $actions = FieldList::create([
            FormAction::create('doResetAccount', 'Reset Account'),
        ]);

        $validation = RequiredFields::create(['NewPassword1', 'NewPassword2']);

        $form = Form::create($this->owner, 'ResetAccountForm', $fields, $actions, $validation);

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
        $member = Member::get()->byID(intval($memberID));

        // Fail if passwords do not match
        if ($data['NewPassword1'] !== $data['NewPassword2']) {
            $form->sessionMessage(
                _t(
                    'SilverStripe\\Security\\Member.ERRORNEWPASSWORD',
                    'You have entered your new password differently, try again'
                ),
                ValidationResult::TYPE_ERROR
            );

            return $this->owner->redirectBack();
        }

        // Check if the new password is accepted
        $validationResult = $member->changePassword($data['NewPassword1']);
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
