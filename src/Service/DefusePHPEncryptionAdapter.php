<?php

namespace SilverStripe\MFA\Service;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\CryptoException;
use SilverStripe\MFA\Exception\EncryptionAdapterException;

/**
 * An encryption adapter for defuse/php-encryption, enabled by default.
 */
class DefusePHPEncryptionAdapter implements EncryptionAdapterInterface
{
    public function encrypt(string $plaintext, string $key): string
    {
        try {
            return Crypto::encryptWithPassword($plaintext, $key);
        } catch (CryptoException $exception) {
            throw new EncryptionAdapterException(
                'Failed to encrypt string with provided key',
                0,
                $exception
            );
        }
    }

    public function decrypt(string $ciphertext, string $key): string
    {
        try {
            return Crypto::decryptWithPassword($ciphertext, $key);
        } catch (CryptoException $exception) {
            throw new EncryptionAdapterException(
                'Failed to decrypt cipher text with provided key',
                0,
                $exception
            );
        }
    }
}
