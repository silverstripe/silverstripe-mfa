<?php

namespace SilverStripe\MFA\Service;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Method\MethodInterface;
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
     * @var string[]
     */
    private static $methods = [];

    /**
     * Get implementations of all configured methods
     *
     * @return MethodInterface[]
     * @throws UnexpectedValueException When an invalid method is registered
     */
    public function getMethods()
    {
        $configuredMethods = (array) $this->config()->get('methods');

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
