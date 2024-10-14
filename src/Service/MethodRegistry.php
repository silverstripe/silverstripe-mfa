<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Service;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\BackupCode\Method;
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
     * A string referring to the classname of the method (implementing SilverStripe\MFA\Method\MethodInterface) that is
     * to be used as the back-up method for MFA. This alters the registration of this method to be required - a forced
     * registration once the user has registered at least one other method. Additionally it cannot be set as the default
     * method for a user to log in with.
     *
     * @config
     * @var string
     */
    private static $default_backup_method = Method::class;

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
     * @throws UnexpectedValueException If a method was registered more than once
     * @throws UnexpectedValueException If multiple registered methods share a common URL segment
     */
    public function getMethods(): array
    {
        if (is_array($this->methodInstances)) {
            return $this->methodInstances;
        }

        $configuredMethods = (array) $this->config()->get('methods');
        $configuredMethods = array_filter($configuredMethods ?? []);
        $this->ensureNoDuplicateMethods($configuredMethods);

        $allMethods = [];
        foreach ($configuredMethods as $method) {
            $method = Injector::inst()->get($method);

            if (!$method instanceof MethodInterface) {
                throw new UnexpectedValueException(sprintf(
                    'Given method "%s" does not implement %s',
                    get_class($method),
                    MethodInterface::class
                ));
            }

            $allMethods[] = $method;
        }
        $this->ensureNoDuplicateURLSegments($allMethods);

        return $this->methodInstances = $allMethods;
    }

    /**
     * Helper method to indicate whether any MFA methods are registered
     *
     * @return bool
     */
    public function hasMethods(): bool
    {
        return count($this->getMethods() ?? []) > 0;
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
        return is_string($configuredBackupMethod) && is_a($method, $configuredBackupMethod ?? '');
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

    /**
     * Ensure that attempts to register a method multiple times do not occur
     *
     * @param array $configuredMethods
     * @throws UnexpectedValueException
     */
    private function ensureNoDuplicateMethods(array $configuredMethods): void
    {
        $uniqueMethods = array_unique($configuredMethods ?? []);
        if ($uniqueMethods === $configuredMethods) {
            return;
        }

        // Get the method class names that were added more than once and format them into a string so we can
        // tell the developer which classes were incorrectly configured
        $duplicates = array_unique(array_diff_key($configuredMethods ?? [], $uniqueMethods));
        $methodNames = implode('; ', $duplicates);
        throw new UnexpectedValueException(
            'Cannot register MFA methods more than once. Check your config: ' . $methodNames
        );
    }

    /**
     * Ensure that all registered methods have a unique URLSegment
     *
     * @param array $allMethods
     * @throws UnexpectedValueException
     */
    private function ensureNoDuplicateURLSegments(array $allMethods): void
    {
        $allURLSegments = array_map(function (MethodInterface $method) {
            return $method->getURLSegment();
        }, $allMethods ?? []);
        $uniqueURLSegments = array_unique($allURLSegments ?? []);
        if ($allURLSegments === $uniqueURLSegments) {
            return;
        }

        // Get the method URL segments that were added more than once and format them into a string so we can
        // tell the developer which classes were incorrectly configured
        $duplicates = array_unique(array_diff_key($allURLSegments ?? [], $uniqueURLSegments));
        $urlSegments = implode('; ', $duplicates);
        throw new UnexpectedValueException(
            'Cannot register multiple MFA methods with the same URL segment: ' . $urlSegments
        );
    }
}
