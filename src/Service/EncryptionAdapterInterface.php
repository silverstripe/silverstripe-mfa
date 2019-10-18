<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Service;

use SilverStripe\MFA\Exception\EncryptionAdapterException;

/**
 * A generic encryption service implementation, responsible for encrypting and decrypting strings.
 */
interface EncryptionAdapterInterface
{
    /**
     * Encrypts the given plain text string with the given key, and returns the output cipher text
     *
     * @param string $plaintext
     * @param string $key
     * @return string Cipher text
     * @throws EncryptionAdapterException
     */
    public function encrypt(string $plaintext, string $key): string;

    /**
     * Decrypts the given cipher text using the given key, and returns the output plain text
     *
     * @param string $ciphertext
     * @param string $key
     * @return string Plain text
     * @throws EncryptionAdapterException
     */
    public function decrypt(string $ciphertext, string $key): string;
}
