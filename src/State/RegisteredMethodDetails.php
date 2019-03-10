<?php

namespace SilverStripe\MFA\State;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\MFA\Method\MethodInterface;

class RegisteredMethodDetails implements RegisteredMethodDetailsInterface
{
    use Injectable;

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

    public function getURLSegment()
    {
        return $this->method->getURLSegment();
    }

    public function getLeadInLabel()
    {
        return $this->method->getLoginHandler()->getLeadInLabel();
    }

    public function getComponent()
    {
        return $this->method->getLoginHandler()->getComponent();
    }

    public function jsonSerialize()
    {
        return [
            'urlSegment' => $this->getURLSegment(),
            'leadInLabel' => $this->getLeadInLabel(),
            'component' => $this->getComponent(),
        ];
    }
}
