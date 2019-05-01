<?php declare(strict_types=1);

namespace SilverStripe\MFA\State;

use SilverStripe\Core\Injector\Injectable;

/**
 * A container for a backup code and its hash, normally used during backup code generation
 */
class BackupCode
{
    use Injectable;

    /**
     * @var string
     */
    protected $code = '';

    /**
     * @var string
     */
    protected $hash = '';

    /**
     * @param string $code
     * @param string $hash
     */
    public function __construct(string $code, string $hash)
    {
        $this->code = $code;
        $this->hash = $hash;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getHash(): string
    {
        return $this->hash;
    }
}
