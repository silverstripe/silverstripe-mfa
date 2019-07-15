<?php

namespace SilverStripe\MFA\Tests\Service;

use SapphireTest;
use SilverStripe\MFA\Service\DefusePHPEncryptionAdapter;

class PHPEncryptionAdapterTest extends SapphireTest
{
    public function testEncryptAndDecrypt()
    {
        $adapter = new DefusePHPEncryptionAdapter();

        $cipher = $adapter->encrypt('Hello World', 'secretkey123');
        $this->assertNotEquals('Hello World', $cipher);

        $plaintext = $adapter->decrypt($cipher, 'secretkey123');
        $this->assertSame('Hello World', $plaintext);
    }

    /**
     * @expectedException \SilverStripe\MFA\Exception\EncryptionAdapterException
     */
    public function testExceptionThrownWhenDecryptionFails()
    {
        (new DefusePHPEncryptionAdapter())->decrypt('892g359gohsdf', '');
    }
}
