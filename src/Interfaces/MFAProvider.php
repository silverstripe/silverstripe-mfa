<?php

namespace Firesphere\BootstrapMFA\Interfaces;

use Firesphere\BootstrapMFA\Models\BackupCode;

interface MFAProvider
{

    /**
     * @param string $token
     * @return bool|BackupCode
     */
    public function fetchToken($token);
}
