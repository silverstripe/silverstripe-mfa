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
            'urlSegment' => $this->method->getURLSegment(),
            'leadInLabel' => $this->method->getLoginHandler()->getLeadInLabel(),
            'component' => $this->method->getLoginHandler()->getComponent(),
            'supportLink' => $this->method->getRegisterHandler()->getSupportLink(),
            'thumbnail' => $this->method->getThumbnail(),
        ];
    }
}
