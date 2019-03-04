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
}
