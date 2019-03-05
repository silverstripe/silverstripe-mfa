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
     * @throws UnexpectedValueException When an invalid method is registered
     */
    public function getMethods()
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
    public function hasMethods()
    {
        return count($this->getMethods()) > 0;
    }

    /**
     * Get an authentication method object matching the given method from the given member. Returns null if the given
     * method could not be found attached to the Member
     *
     * @param Member|MemberExtension $member
     * @param string $specifiedMethod The class name of the requested method
     * @return RegisteredMethod|null
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

        return $method;
    }

    /**
     * Fetches a Method by its URL Segment
     *
     * @param string $segment
     * @return MethodInterface|null
     */
    public function getMethodByURLSegment($segment)
    {
        foreach ($this->getMethods() as $method) {
            if ($method->getURLSegment() === $segment) {
                return $method;
            }
        }

        return null;
    }
}
