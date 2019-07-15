<?php declare(strict_types=1);

namespace SilverStripe\MFA\BackupCode;

use Injector;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\Handler\VerifyHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;

class Method implements MethodInterface
{
    /**
     * Get a URL segment for this method. This will be used in URL paths for performing authentication by this method
     *
     * @return string
     */
    public function getURLSegment(): string
    {
        return 'backup-codes';
    }

    /**
     * Return the VerifyHandler that is used to start and check verification attempts with this method
     *
     * @return VerifyHandlerInterface
     */
    public function getVerifyHandler(): VerifyHandlerInterface
    {
        return Injector::inst()->create(VerifyHandler::class);
    }

    /**
     * Return the RegisterHandler that is used to perform registrations with this method
     *
     * @return RegisterHandlerInterface
     */
    public function getRegisterHandler(): RegisterHandlerInterface
    {
        return Injector::inst()->create(RegisterHandler::class);
    }

    public function getThumbnail(): string
    {
        return '/mfa/client/dist/images/locked-letter.svg';
    }

    public function applyRequirements(): void
    {
        // This authenticator bundles client requirements in the main bundle.
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getUnavailableMessage(): string
    {
        return '';
    }
}
