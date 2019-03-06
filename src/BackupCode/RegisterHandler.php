<?php

namespace SilverStripe\MFA\BackupCode;

use Exception;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extensible;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Store\StoreInterface;

class RegisterHandler implements RegisterHandlerInterface
{
    use Extensible;

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
                for ($j = 0; $j < $codeLength; $j += 9) {
                    $code .= str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
                }
                $code = substr($code, 0, $codeLength);
            } while (in_array($code, $codes));
            $codes[] = $code;
        }

        $this->extend('updateBackupCodes', $codes);

        // Create hashes for these codes
        $hashedCodes = array_map([$this, 'hashCode'], $codes);

        $store->setState($hashedCodes);

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
        return $store->getState();
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
        return _t(__CLASS__ . '.NAME', 'Backup codes');
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
            . 'available. Each code can only be used once.' . PHP_EOL . PHP_EOL . 'Please store these codes somewhere '
            . 'safe, as they will not be viewable after this leaving this page.'
        );
    }

    /**
     * Provide a localised URL to a support article about the registration process for this MFA Method.
     *
     * @return string
     */
    public function getSupportLink()
    {
        return '';
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
}
