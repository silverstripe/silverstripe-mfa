<?php

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

    public function jsonSerialize()
    {
        return [
            'name' => $this->method->getRegisterHandler()->getName(),
            'urlSegment' => $this->method->getURLSegment(),
            'isAvailable' => $this->method->isAvailable(),
            'leadInLabel' => $this->method->getVerifyHandler()->getLeadInLabel(),
            'component' => $this->method->getVerifyHandler()->getComponent(),
            'supportLink' => $this->method->getRegisterHandler()->getSupportLink(),
            'thumbnail' => $this->method->getThumbnail(),
        ];
    }
}
