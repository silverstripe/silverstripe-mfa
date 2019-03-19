<?php

namespace SilverStripe\MFA\BackupCode;

use Exception;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Store\StoreInterface;

class RegisterHandler implements RegisterHandlerInterface
{
    use Extensible;
    use Configurable;

    /**
     * Provide a user help link that will be available when registering backup codes
     * TODO Will this have a user help link as a default?
     *
     * @config
     * @var string
     */
    private static $user_help_link;

    /**
     * Stores any data required to handle a registration process with a method, and returns relevant state to be applied
     * to the front-end application managing the process.
     *
     * @param StoreInterface $store An object that hold session data (and the Member) that can be mutated
     * @return array Props to be passed to a front-end component
     * @throws Exception When there is no valid source of CSPRNG
     */
    public function start(StoreInterface $store)
    {
        // Generate backup codes
        $codeCount = (int) Config::inst()->get(Method::class, 'backup_code_count') ?: 9;
        $codeLength = (int) Config::inst()->get(Method::class, 'backup_code_length') ?: 6;

        $codes = [];

        for ($i = 0; $i < $codeCount; $i++) {
            // Wrap in a "do while" to ensure there are no duplicates
            do {
                $code = '';
                // We can only generate 9 digits at a time on 32 bit systems
                for ($j = 0; $j < $codeLength; $j++) {
                    $code .= (string) random_int(0, 9);
                }
            } while (in_array($code, $codes));
            $codes[] = $code;
        }

        $this->extend('updateBackupCodes', $codes);

        // Create hashes for these codes
        $hashedCodes = array_map([$this, 'hashCode'], $codes);

        // Create or update the RegisteredMethod on the member. This breaks the normal flow as it's created on "start"
        // instead of after receiving a response from the user

        /** @var MethodInterface $method */
        $method = Injector::inst()->get(Method::class);

        RegisteredMethodManager::singleton()->registerForMember($store->getMember(), $method, $hashedCodes);

        // Return unhashed codes for the front-end UI
        return [
            'codes' => $codes,
        ];
    }

    /**
     * Confirm that the provided details are valid, and create a new RegisteredMethod against the member.
     *
     * @param HTTPRequest $request
     * @param StoreInterface $store
     * @return array
     */
    public function register(HTTPRequest $request, StoreInterface $store)
    {
        // Backup codes are unique where no confirmation or user input is required. The method is registered on "start"
        return [];
    }

    /**
     * Provide a localised name for this MFA Method.
     *
     * eg. "Authenticator app"
     *
     * @return string
     */
    public function getName()
    {
        return _t(__CLASS__ . '.NAME', 'Backup recovery codes');
    }

    /**
     * Provide a localised description of this MFA Method.
     *
     * eg. "Verification codes are created by an app on your phone"
     *
     * @return string
     */
    public function getDescription()
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
    public function getSupportLink()
    {
        return (string) $this->config()->get('user_help_link');
    }

    /**
     * Hash a back-up code for storage. This uses the native PHP password_hash API but can be extended to implement a
     * custom hash requirement.
     *
     * @param string $code
     * @return bool|string
     */
    protected function hashCode($code)
    {
        return password_hash($code, PASSWORD_DEFAULT);
    }

    /**
     * Get the key that a React UI component is registered under (with @silverstripe/react-injector on the front-end)
     *
     * @return string
     */
    public function getComponent()
    {
        return 'BackupCodeRegister';
    }
}
