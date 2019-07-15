<?php declare(strict_types=1);

namespace SilverStripe\MFA\Extension\AccountReset;

use Exception;
use SS_Log;
use Controller;
use Email;
use SS_HTTPRequest as HTTPRequest;
use SS_HTTPResponse as HTTPResponse;
use Extension;
use SecurityAdmin;
use SilverStripe\MFA\Extension\MemberExtension as BaseMFAMemberExtension;
use SilverStripe\MFA\JSONResponse;
use ValidationException;
use Member;
use PasswordEncryptor_NotFoundException;
use Permission;
use Security;
use SecurityToken;

/**
 * This extension is applied to SecurityAdmin to provide an additional endpoint
 * for sending account reset requests.
 *
 * @package SilverStripe\MFA\Extension
 * @property SecurityAdmin $owner
 */
class SecurityAdminExtension extends Extension
{
    use JSONResponse;

    private static $allowed_actions = [
        'reset',
    ];

    public function reset(HTTPRequest $request): HTTPResponse
    {
        if (!$request->isPOST() || !$request->param('ID')) {
            return $this->jsonResponse(
                [
                    'error' => _t(__CLASS__ . '.BAD_REQUEST', 'Invalid request')
                ],
                400
            );
        }

        $body = json_decode($request->getBody() ?? '', true);

        if (!SecurityToken::inst()->check($body['csrf_token'] ?? null)) {
            return $this->jsonResponse(
                [
                    'error' => _t(__CLASS__ . '.INVALID_CSRF_TOKEN', 'Invalid or missing CSRF token')
                ],
                400
            );
        }

        if (!Permission::check(BaseMFAMemberExtension::MFA_ADMINISTER_REGISTERED_METHODS)) {
            return $this->jsonResponse(
                [
                    'error' => _t(
                        __CLASS__ . '.INSUFFICIENT_PERMISSIONS',
                        'Insufficient permissions to reset user'
                    )
                ],
                403
            );
        }

        /** @var Member $memberToReset */
        $memberToReset = Member::get()->byID($request->param('ID'));

        if ($memberToReset === null) {
            return $this->jsonResponse(
                [
                    'error' => _t(
                        __CLASS__ . '.INVALID_MEMBER',
                        'Requested member for reset not found'
                    )
                ],
                403
            );
        }

        $sent = $this->sendResetEmail($memberToReset);

        if (!$sent) {
            return $this->jsonResponse(
                [
                    'error' => _t(
                        __CLASS__ . '.EMAIL_NOT_SENT',
                        'Email sending failed'
                    )
                ],
                500
            );
        }

        return $this->jsonResponse(['success' => true], 200);
    }

    /**
     * Prepares and attempts to send the Account Reset request email.
     *
     * @param Member&MemberExtension $member
     * @return bool
     */
    protected function sendResetEmail($member)
    {
        // Generate / store / obtain reset token
        $token = $member->generateAccountResetTokenAndStoreHash();

        // Create email and fire
        try {
            $email = Email::create()
                ->setTemplate('AccountReset')
                ->populateTemplate($member)
                ->setSubject(_t(
                    __CLASS__ . '.ACCOUNT_RESET_EMAIL_SUBJECT',
                    'Reset your account'
                ))
                ->populateTemplate(['AccountResetLink' => $this->getAccountResetLink($member, $token)])
                ->populateTemplate(['Member' => $member])
                ->setFrom(Email::config()->get('admin_email'))
                ->setTo($member->Email);

            return $email->send();
        } catch (Exception $e) {
            SS_Log::log('WARNING: Account Reset Email failed to send: ' . $e->getMessage(), SS_Log::INFO);
            return false;
        }
    }

    /**
     * Generates a link to the Account Reset Handler endpoint to be sent to a Member.
     *
     * @param Member $member
     * @param string $token
     * @return string
     */
    public function getAccountResetLink(Member $member, string $token): string
    {
        return Controller::join_links(
            Security::singleton()->Link('resetaccount'),
            "?m={$member->ID}&t={$token}"
        );
    }
}
