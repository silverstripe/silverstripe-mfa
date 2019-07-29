<?php declare(strict_types=1);

namespace SilverStripe\MFA\State;

use SilverStripe\MFA\Method\MethodInterface;

class RegisteredMethodDetails implements RegisteredMethodDetailsInterface
{
    /**
     * @var MethodInterface
     */
    private $method;

    /**
     * @param MethodInterface $method
     */
    public function __construct(MethodInterface $method)
    {
        $this->method = $method;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->method->getName(),
            'urlSegment' => $this->method->getURLSegment(),
            'isAvailable' => $this->method->isAvailable(),
            'unavailableMessage' => $this->method->getUnavailableMessage(),
            'component' => $this->method->getVerifyHandler()->getComponent(),
            'supportLink' => $this->method->getRegisterHandler()->getSupportLink(),
            'thumbnail' => $this->method->getThumbnail(),
        ];
    }
}
