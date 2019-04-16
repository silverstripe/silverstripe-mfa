<?php declare(strict_types=1);

namespace SilverStripe\MFA\Service;

/**
 * A service class implementation for generating and hashing backup codes.
 */
interface BackupCodeGeneratorInterface
{
    /**
     * Generate a list of backup codes and return them in a key -> value pair of plain text to hashed values.
     *
     * @return string[] Key value pairs of plaintext and hashes
     */
    public function generate(): array;

    /**
     * Hash the given backup code for storage.
     *
     * @param string $code
     * @return string
     */
    public function hash(string $code): string;

    /**
     * Returns a list of possible characters to use in backup codes.
     *
     * @return array
     */
    public function getCharacterSet(): array;
}
