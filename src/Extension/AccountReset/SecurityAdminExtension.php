<?php declare(strict_types=1);

namespace SilverStripe\MFA\Extension\AccountReset;

use Exception;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;
use SilverStripe\Admin\SecurityAdmin;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\JSONResponse;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Member;
use SilverStripe\Security\PasswordEncryptor_NotFoundException;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use SilverStripe\Security\SecurityToken;

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

        if (!Permission::check(MemberExtension::MFA_ADMINISTER_REGISTERED_METHODS)) {
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
     * @param Member&MemberResetExtension $member
     * @return bool
     * @throws ValidationException
     * @throws PasswordEncryptor_NotFoundException
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
                ->setFrom(Email::config()->get('admin_email'))
                ->setTo($member->Email);

            return $email->send();
        } catch (Exception $e) {
            $this->logger->info('WARNING: Account Reset Email failed to send: ' . $e->getMessage());
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
    protected function getAccountResetLink(Member $member, string $token): string
    {
        return Security::singleton()->Link('resetaccount') . "?m={$member->ID}&t={$token}";
    }
}
