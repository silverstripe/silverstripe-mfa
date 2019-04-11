<?php

namespace SilverStripe\MFA\State;

use SilverStripe\MFA\Method\MethodInterface;

class AvailableMethodDetails implements AvailableMethodDetailsInterface
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
            'name' => $this->method->getRegisterHandler()->getName(),
            'description' => $this->method->getRegisterHandler()->getDescription(),
            'supportLink' => $this->method->getRegisterHandler()->getSupportLink(),
            'thumbnail' => $this->method->getThumbnail(),
            'component' => $this->method->getRegisterHandler()->getComponent(),
            'isAvailable' => $this->method->isAvailable(),
            'unavailableMessage' => $this->method->getUnavailableMessage(),
        ];
    }
}
