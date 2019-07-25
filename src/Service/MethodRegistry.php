<?php declare(strict_types=1);

namespace SilverStripe\MFA\Service;

use Injector;
use SilverStripe\MFA\Method\MethodInterface;
use SS_Object;
use UnexpectedValueException;

/**
 * A service class that holds the configuration for enabled MFA methods and facilitates providing these methods
 */
class MethodRegistry extends SS_Object
{

    /**
     * List of configured MFA methods. These should be class names that implement MethodInterface
     *
     * @config
     * @var string[]
     */
    private static $methods = [];

    /**
     * A string referring to the classname of the method (implementing SilverStripe\MFA\Method\MethodInterface) that is
     * to be used as the back-up method for MFA. This alters the registration of this method to be required - a forced
     * registration once the user has registered at least one other method. Additionally it cannot be set as the default
     * method for a user to log in with.
     *
     * @config
     * @var string
     */
    private static $default_backup_method = 'SilverStripe\\MFA\\BackupCode\\Method';

    /**
     * Request cache of instantiated method instances
     *
     * @var MethodInterface[]
     */
    protected $methodInstances;

    /**
     * Get implementations of all configured methods
     *
     * @return MethodInterface[]
     * @throws UnexpectedValueException When an invalid method is registered
     */
    public function getMethods(): array
    {
        if (is_array($this->methodInstances)) {
            return $this->methodInstances;
        }

        $configuredMethods = (array) $this->config()->get('methods');
        $configuredMethods = array_filter($configuredMethods);

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

        return $this->methodInstances = $allMethods;
    }

    /**
     * Helper method to indicate whether any MFA methods are registered
     *
     * @return bool
     */
    public function hasMethods(): bool
    {
        return count($this->getMethods()) > 0;
    }

    /**
     * Indicates whether the given method is registered as the back-up method for MFA
     *
     * @param MethodInterface $method
     * @return bool
     */
    public function isBackupMethod(MethodInterface $method): bool
    {
        $configuredBackupMethod = $this->config()->get('default_backup_method');
        return is_string($configuredBackupMethod) && is_a($method, $configuredBackupMethod);
    }

    /**
     * Get the configured backup method
     *
     * @return MethodInterface|null
     */
    public function getBackupMethod(): ?MethodInterface
    {
        foreach ($this->getMethods() as $method) {
            if ($this->isBackupMethod($method)) {
                return $method;
            }
        }

        return null;
    }

    /**
     * Fetches a Method by its URL Segment
     *
     * @param string $segment
     * @return MethodInterface|null
     */
    public function getMethodByURLSegment(string $segment): ?MethodInterface
    {
        foreach ($this->getMethods() as $method) {
            if ($method->getURLSegment() === $segment) {
                return $method;
            }
        }

        return null;
    }
}
