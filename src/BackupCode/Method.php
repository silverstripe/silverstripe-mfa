<?php

namespace SilverStripe\MFA\BackupCode;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Method\Handler\LoginHandlerInterface;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\State\AvailableMethodDetailsInterface;

class Method implements MethodInterface
{
    /**
     * The number of back-up codes that should be generated for a user. Note that changing this value will not
     * regenerate or generate new codes to meet the new number. The user will have to manually regenerate codes to
     * receive the new number of codes.
     *
     * @config
     * @var int
     */
    private static $backup_code_count = 9;

    /**
     * The length of each individual backup code
     *
     * @config
     * @var int
     */
    private static $backup_code_length = 6;

    /**
     * Get a URL segment for this method. This will be used in URL paths for performing authentication by this method
     *
     * @return string
     */
    public function getURLSegment()
    {
        return 'backup-codes';
    }

    /**
     * Return the LoginHandler that is used to start and verify login attempts with this method
     *
     * @return LoginHandlerInterface
     */
    public function getLoginHandler()
    {
        return Injector::inst()->create(LoginHandler::class);
    }

    /**
     * Return the RegisterHandler that is used to perform registrations with this method
     *
     * @return RegisterHandlerInterface
     */
    public function getRegisterHandler()
    {
        return Injector::inst()->create(RegisterHandler::class);
    }

    public function getDetails()
    {
        return Injector::inst()->create(AvailableMethodDetailsInterface::class, $this);
    }
}
