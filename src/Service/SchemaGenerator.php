<?php

namespace SilverStripe\MFA\Service;

use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\MFA\Extension\MemberExtension;
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
     * Gets the schema data for the multi factor authentication app, using the current Member as context
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
            return $methodDetails->getURLSegment();
        }, $registeredMethods);

        $schema = [
            'registeredMethods' => $registeredMethods,
            'availableMethods' => $this->getAvailableMethods($exclude),
            'defaultMethod' => $this->getDefaultMethod($member),
            'canSkip' => $enforcementManager->canSkipMFA($member),
            'shouldRedirect' => $enforcementManager->shouldRedirectToMFA($member),
        ];

        $this->extend('updateSchema', $schema);

        return $schema;
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
            $registeredMethodDetails[] = $registeredMethod->getDetails();
        }
        return $registeredMethodDetails;
    }

    /**
     * Get details in a list for all available methods, optionally excluding those with urlSegments provided in
     * $exclude
     *
     * @param array $exclude
     * @return AvailableMethodDetailsInterface[]
     */
    protected function getAvailableMethods(array $exclude = [])
    {
        // Prepare an array to hold details for methods available to register
        $availableMethods = [];

        // Get all methods enabled on the site
        $allMethods = MethodRegistry::singleton()->getMethods();

        // Compile details for methods that aren't already registered to the user
        foreach ($allMethods as $method) {
            if (in_array($method->getURLSegment(), $exclude)) {
                continue;
            }
            $availableMethods[] = $method->getDetails();
        }

        return $availableMethods;
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
}
