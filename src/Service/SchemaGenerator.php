<?php

namespace SilverStripe\MFA\Service;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\MFA\Authenticator\LoginHandler;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\State\AvailableMethodDetailsInterface;
use SilverStripe\MFA\State\RegisteredMethodDetailsInterface;
use SilverStripe\Security\Member;

/**
 * Generates a multi-factor authentication frontend app schema from the given request
 */
class SchemaGenerator
{
    use Extensible;
    use Injectable;

    /**
     * Provides the full schema for the multi-factor authentication app, using the current Member as context
     *
     * @param Member&MemberExtension $member
     * @return array
     */
    public function getSchema(Member $member)
    {
        $enforcementManager = EnforcementManager::singleton();
        $registeredMethods = $this->getRegisteredMethods($member);

        // Skip registration details if the user has already registered this method
        $exclude = array_map(function (RegisteredMethodDetailsInterface $methodDetails) {
            return $methodDetails->jsonSerialize()['urlSegment'];
        }, $registeredMethods);

        // Also skip the backup method
        $backupMethod = MethodRegistry::singleton()->getBackupMethod();
        if ($backupMethod) {
            $exclude = array_merge($exclude, [$backupMethod->getURLSegment()]);
        }

        $schema = [
            'registeredMethods' => $registeredMethods,
            'availableMethods' => $this->getAvailableMethods($exclude),
            'allMethods' => $this->getAvailableMethods(),
            'defaultMethod' => $this->getDefaultMethod($member),
            'backupMethod' => $this->getBackupMethod(),
            'canSkip' => $enforcementManager->canSkipMFA($member),
            'isFullyRegistered' => $enforcementManager->hasCompletedRegistration($member),
            'resources' => $this->getResources(),
            'shouldRedirect' => $enforcementManager->shouldRedirectToMFA($member),
        ];

        $this->extend('updateSchema', $schema);

        return $schema;
    }

    /**
     * Get details in a list for all available methods, optionally excluding those with urlSegments provided in
     * $exclude
     *
     * @param array $exclude
     * @return AvailableMethodDetailsInterface[]
     */
    public function getAvailableMethods(array $exclude = [])
    {
        // Prepare an array to hold details for methods available to register
        $availableMethods = [];

        // Get all methods enabled on the site
        $methodRegistry = MethodRegistry::singleton();
        $allMethods = $methodRegistry->getMethods();

        // Compile details for methods that aren't already registered to the user
        foreach ($allMethods as $method) {
            // Omit specified exclusions or methods that are configured as back-up methods
            if (in_array($method->getURLSegment(), $exclude)) {
                continue;
            }
            $availableMethods[] = Injector::inst()->create(AvailableMethodDetailsInterface::class, $method);
        }

        return $availableMethods;
    }

    /**
     * Get a list of methods registered to the user
     *
     * @param Member&MemberExtension $member
     * @return RegisteredMethodDetailsInterface[]
     */
    protected function getRegisteredMethods(Member $member)
    {
        $registeredMethodDetails = [];
        foreach ($member->RegisteredMFAMethods() as $registeredMethod) {
            $registeredMethodDetails[] = Injector::inst()->create(
                RegisteredMethodDetailsInterface::class,
                $registeredMethod->getMethod()
            );
        }
        return $registeredMethodDetails;
    }

    /**
     * Get the URL Segment for the configured default method on the current member, or null if none is configured
     *
     * @param Member&MemberExtension $member
     * @return string|null
     */
    protected function getDefaultMethod(Member $member)
    {
        $defaultMethod = $member->DefaultRegisteredMethod;
        return $defaultMethod ? $defaultMethod->getMethod()->getURLSegment() : null;
    }

    /**
     * Get the "details" of the configured back-up method (if set)
     *
     * @return AvailableMethodDetailsInterface|null
     */
    protected function getBackupMethod()
    {
        $methodClass = Config::inst()->get(MethodRegistry::class, 'default_backup_method');
        if (!$methodClass) {
            return null;
        }

        /** @var MethodInterface $method */
        $method = Injector::inst()->create($methodClass);

        return $method ? Injector::inst()->create(AvailableMethodDetailsInterface::class, $method) : null;
    }

    /**
     * Provide URLs for resources such as images and help articles
     */
    protected function getResources()
    {
        $module = ModuleLoader::getModule('silverstripe/mfa');

        return [
            'user_help_link' => Config::inst()->get(LoginHandler::class, 'user_help_link'),
            'extra_factor_image_url' => $module->getResource('client/dist/images/extra-protection.svg')->getURL(),
            'unique_image_url' => $module->getResource('client/dist/images/unique.svg')->getURL(),
            'more_options_image_url' => $module->getResource('client/dist/images/more-options.svg')->getURL(),
        ];
    }
}
