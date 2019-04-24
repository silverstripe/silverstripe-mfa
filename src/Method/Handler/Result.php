<?php declare(strict_types=1);

namespace SilverStripe\MFA\Method\Handler\Result;

use SilverStripe\Core\Injector\Injectable;

/**
 * An immutable result object detailing the result of a registration or validation completed by the respective handlers
 */
class Result
{
    use Injectable;

    /**
     * Indicates this result was successful
     *
     * @var bool
     */
    protected $success;

    /**
     * An message describing the result
     *
     * @var string
     */
    protected $message = '';

    /**
     * Context provided by the handler returning this result
     *
     * @var array
     */
    protected $context = [];

    /**
     * @param bool $success
     * @param string $message
     * @param array $context
     */
    public function __construct(bool $success = true, string $message = '', array $context = [])
    {
        $this->success = $success;
        $this->message = $message;
        $this->context = $context;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param bool $success
     * @return Result
     */
    public function setSuccess(bool $success): Result
    {
        return new static($success, $this->getMessage(), $this->getContext());
    }

    /**
     * @param string $message
     * @return Result
     */
    public function setMessage(string $message): Result
    {
        return new static($this->isSuccessful(), $message, $this->getContext());
    }

    /**
     * @param array $context
     * @return Result
     */
    public function setContext(array $context): Result
    {
        return new static($this->isSuccessful(), $this->getMessage(), $context);
    }
}
