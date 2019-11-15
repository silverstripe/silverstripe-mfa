<?php

declare(strict_types=1);

namespace SilverStripe\MFA\BackupCode;

use Exception;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Exception\RegistrationFailedException;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Service\BackupCodeGeneratorInterface;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\State\BackupCode;
use SilverStripe\MFA\State\Result;
use SilverStripe\MFA\Store\StoreInterface;

class RegisterHandler implements RegisterHandlerInterface
{
    use Extensible;
    use Configurable;

    /**
     * Provide a user help link that will be available when registering backup codes
     *
     * @config
     * @var string
     */
    // phpcs:disable
    private static $user_help_link = 'https://userhelp.silverstripe.org/en/4/optional_features/multi-factor_authentication/user_manual/regaining_access/';
    // phpcs:enable

    /**
     * Stores any data required to handle a registration process with a method, and returns relevant state to be applied
     * to the front-end application managing the process.
     *
     * @param StoreInterface $store An object that hold session data (and the Member) that can be mutated
     * @return array Props to be passed to a front-end component
     * @throws Exception When there is no valid source of CSPRNG
     * @throws RegistrationFailedException If no registered methods are defined for the member
     */
    public function start(StoreInterface $store): array
    {
        $member = $store->getMember();
        if (!$member || !$member->RegisteredMFAMethods()->exists()) {
            throw new RegistrationFailedException(
                'Attempted to register backup codes with no registered methods'
            );
        }

        // Create or update the RegisteredMethod on the member. This breaks the normal flow as it's created on "start"
        // instead of after receiving a response from the user

        /** @var MethodInterface $method */
        $method = Injector::inst()->get(Method::class);

        /** @var BackupCodeGeneratorInterface $generator */
        $generator = Injector::inst()->get(BackupCodeGeneratorInterface::class);
        $codes = $generator->generate();

        RegisteredMethodManager::singleton()->registerForMember(
            $member,
            $method,
            array_map(function (BackupCode $backupCode) {
                return json_encode($backupCode);
            }, $codes)
        );

        // Return un-hashed codes for the front-end UI
        return [
            'codes' => array_map(function (BackupCode $backupCode) {
                return $backupCode->getCode();
            }, $codes),
        ];
    }

    /**
     * Confirm that the provided details are valid, and create a new RegisteredMethod against the member.
     *
     * @param HTTPRequest $request
     * @param StoreInterface $store
     * @return array
     */
    public function register(HTTPRequest $request, StoreInterface $store): Result
    {
        // Backup codes are unique where no confirmation or user input is required. The method is registered on "start"
        return Result::create();
    }

    /**
     * Provide a localised description of this MFA Method.
     *
     * eg. "Verification codes are created by an app on your phone"
     *
     * @return string
     */
    public function getDescription(): string
    {
        return _t(
            __CLASS__ . '.DESCRIPTION',
            'Recovery codes enable you to log into your account in the event your primary authentication is not '
            . 'available. Each code can only be used once. Store these codes somewhere safe, as they will not be '
            . 'viewable after this leaving this page.'
        );
    }

    /**
     * Provide a localised URL to a support article about the registration process for this MFA Method.
     *
     * @return string
     */
    public function getSupportLink(): string
    {
        return (string) $this->config()->get('user_help_link');
    }

    /**
     * Provide a localised string to describe the support link {@see getSupportLink} about this MFA Method.
     *
     * @return string
     */
    public function getSupportText(): string
    {
        return _t(__CLASS__ . '.SUPPORT_LINK_DESCRIPTION', 'Learn about recovery codes.');
    }

    /**
     * Get the key that a React UI component is registered under (with @silverstripe/react-injector on the front-end)
     *
     * @return string
     */
    public function getComponent(): string
    {
        return 'BackupCodeRegister';
    }
}
