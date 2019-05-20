<?php

namespace SilverStripe\MFA\Extension;

use Exception;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Extension;
use SilverStripe\Admin\SecurityAdmin;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Member;
use SilverStripe\Security\PasswordEncryptor_NotFoundException;
use SilverStripe\Security\Permission;

/**
 * This extension is applied to SecurityAdmin to provide an additional endpoint
 * for sending account reset requests.
 *
 * @package SilverStripe\MFA\Extension
 * @property SecurityAdmin $owner
 */
class SecurityAdminAccountResetExtension extends Extension
{
    private static $allowed_actions = [
        'reset',
    ];

    public function reset(HTTPRequest $request): HTTPResponse
    {
        if (!$request->isPOST() || !$request->param('ID')) {
            return $this->owner
                ->getResponse()
                ->setStatusCode(400)
                ->addHeader('Content-Type', 'application/json')
                ->setBody(json_encode(
                    [
                        'error' => _t(__CLASS__ . '.BAD_REQUEST', 'Invalid request')
                    ]
                ));
        }

        if (!Permission::check(MemberMFAExtension::MFA_ADMINISTER_REGISTERED_METHODS)) {
            return $this->owner
                ->getResponse()
                ->setStatusCode(403)
                ->addHeader('Content-Type', 'application/json')
                ->setBody(json_encode(
                    [
                        'error' => _t(
                            __CLASS__ . '.INSUFFICIENT_PERMISSIONS',
                            'Insufficient permissions to reset user'
                        )
                    ]
                ));
        }

        /** @var Member $memberToReset */
        $memberToReset = Member::get()->byID($request->param('ID'));

        if ($memberToReset === null) {
            return $this->owner
                ->getResponse()
                ->setStatusCode(403)
                ->addHeader('Content-Type', 'application/json')
                ->setBody(json_encode(
                    [
                        'error' => _t(
                            __CLASS__ . '.INVALID_MEMBER',
                            'Requested member for reset not found'
                        )
                    ]
                ));
        }

        $sent = $this->sendResetEmail($memberToReset);

        if (!$sent) {
            return $this->owner
                ->getResponse()
                ->setStatusCode(500)
                ->addHeader('Content-Type', 'application/json')
                ->setBody(json_encode(
                    [
                        'error' => _t(
                            __CLASS__ . '.EMAIL_NOT_SENT',
                            'Email sending failed'
                        )
                    ]
                ));
        }

        return $this->owner
            ->getResponse()
            ->setStatusCode(200)
            ->addHeader('Content-Type', 'application/json')
            ->setBody(json_encode(
                [
                    'success' => true,
                ]
            ));
    }

    /**
     * @param Member&MemberResetExtension $member
     * @return bool
     * @throws ValidationException
     * @throws PasswordEncryptor_NotFoundException
     */
    private function sendResetEmail($member)
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
                ->setFrom(Email::config()->admin_email)
                ->setTo($member->Email);

            return $email->send();
        } catch (Exception $e) {
            Injector::inst()->get(LoggerInterface::class)->info('WARNING: Account Reset Email failed to send');
            return false;
        }
    }

    private function getAccountResetLink(Member $member, string $token)
    {
        return null;
    }
}
