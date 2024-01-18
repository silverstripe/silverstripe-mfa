<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Extension\AccountReset;

use Exception;
use Psr\Log\LoggerInterface;
use SilverStripe\Admin\SecurityAdmin;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;
use SilverStripe\MFA\Extension\MemberExtension as BaseMFAMemberExtension;
use SilverStripe\MFA\JSONResponse;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\Security\SecurityToken;

/**
 * This extension is applied to SecurityAdmin to provide an additional endpoint
 * for sending account reset requests.
 *
 * @extends Extension<SecurityAdmin>
 */
class SecurityAdminExtension extends Extension
{
    use JSONResponse;

    private static $allowed_actions = [
        'reset',
    ];

    private static $url_handlers = [
        'users/reset/$ID' => 'reset',
    ];

    /**
     * @var string[]
     */
    private static $dependencies = [
        'Logger' => '%$' . LoggerInterface::class . '.account_reset',
    ];

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
                ->setHTMLTemplate('SilverStripe\\MFA\\Email\\AccountReset')
                ->setData($member)
                ->setSubject(_t(
                    __CLASS__ . '.ACCOUNT_RESET_EMAIL_SUBJECT',
                    'Reset your account'
                ))
                ->addData('AccountResetLink', $this->getAccountResetLink($member, $token))
                ->addData('Member', $member)
                ->setTo($member->Email);
            $email->send();
        } catch (Exception $e) {
            $this->logger->info('WARNING: Account Reset Email failed to send: ' . $e->getMessage());
            return false;
        }

        return true;
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

    /**
     * @param LoggerInterface|null $logger
     * @return SecurityAdmin
     */
    public function setLogger(?LoggerInterface $logger): ?SecurityAdmin
    {
        $this->logger = $logger;
        return $this->owner;
    }
}
