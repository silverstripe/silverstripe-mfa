<?php declare(strict_types=1);

namespace SilverStripe\MFA\Service;

use SilverStripe\MFA\Exception\HashFailedException;
use SilverStripe\MFA\State\BackupCode;

/**
 * A service class implementation for generating and hashing backup codes.
 */
interface BackupCodeGeneratorInterface
{
    /**
     * Generate a list of backup codes and return them in an array of state objects.
     *
     * @return BackupCode[]
     */
    public function generate(): array;

    /**
     * Hash the given backup code for storage. May throw an exception if hashing fails, or if the hash
     * is the same as the input plaintext code.
     *
     * @param string $code
     * @return string
     * @throws HashFailedException
     */
    public function hash(string $code): string;

    /**
     * Returns a list of possible characters to use in backup codes.
     *
     * @return array
     */
    public function getCharacterSet(): array;
}
