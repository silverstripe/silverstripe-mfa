<?php

namespace SilverStripe\MFA\Service;

use LogicException;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\Security\Member;
use UnexpectedValueException;

/**
 * A service class that holds the configuration for enabled MFA methods and facilitates providing these methods
 */
class MethodRegistry
{
    use Configurable;
    use Injectable;

    /**
     * List of configured MFA methods. These should be class names that implement MethodInterface
     *
     * @config
     * @var array
     */
    private static $methods;

    /**
     * Get implementations of all configured methods
     *
     * @return MethodInterface[]
     */
    public function getAllMethods()
    {
        $configuredMethods = (array) static::config()->get('methods');

        $allMethods = [];

        foreach ($configuredMethods as $method) {
            $method = Injector::inst()->get($method);

            if (!$method instanceof MethodInterface) {
                throw new UnexpectedValueException(sprintf(
                    'Given method "%s" does not implement %s',
                    $method,
                    MethodInterface::class
                ));
            }

            $allMethods[] = $method;
        }

        return $allMethods;
    }

    /**
     * Helper method to indicate whether any MFA methods are registered
     *
     * @return bool
     */
    public function areMethodsAvailable()
    {
        return count($this->getAllMethods()) > 0;
    }

    /**
     * Get an authentication method object matching the given method from the given member.
     *
     * @param Member|MemberExtension $member
     * @param string $specifiedMethod
     * @return RegisteredMethod
     */
    public function getMethodFromMember(Member $member, $specifiedMethod)
    {
        $method = null;

        // Find the actual method registration data object from the member for the specified default authenticator
        foreach ($member->RegisteredMFAMethods() as $candidate) {
            if ($candidate->MethodClassName === $specifiedMethod) {
                $method = $candidate;
                break;
            }
        }

        // In this scenario the member has managed to set a default authenticator that has no registration.
        if (!$method) {
            throw new LogicException(sprintf(
                'There is no authenticator registered for this member that matches the requested method ("%s")',
                $specifiedMethod
            ));
        }

        return $method;
    }
}
