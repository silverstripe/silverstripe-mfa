<?php

declare(strict_types=1);

namespace SilverStripe\MFA\BackupCode;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\MFA\Method\Handler\VerifyHandlerInterface;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;

class Method implements MethodInterface
{
    /**
     * Provide a localised name for this MFA Method.
     *
     * eg. "Authenticator app"
     *
     * @return string
     */
    public function getName(): string
    {
        return _t(__CLASS__ . '.NAME', 'Recovery codes');
    }

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
        return (string) ModuleLoader::getModule('silverstripe/mfa')
            ->getResource('client/dist/images/locked-letter.svg')
            ->getURL();
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
