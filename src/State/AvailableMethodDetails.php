<?php

namespace SilverStripe\MFA\State;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\MFA\Method\MethodInterface;

class AvailableMethodDetails implements AvailableMethodDetailsInterface
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

    public function getName()
    {
        return $this->method->getRegisterHandler()->getName();
    }

    public function getDescription()
    {
        return $this->method->getRegisterHandler()->getDescription();
    }

    public function getSupportLink()
    {
        return $this->method->getRegisterHandler()->getSupportLink();
    }

    public function jsonSerialize()
    {
        return [
            'urlSegment' => $this->getURLSegment(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'supportLink' => $this->getSupportLink(),
        ];
    }
}
