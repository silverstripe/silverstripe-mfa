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

    public function getURLSegment(): string
    {
        return $this->method->getURLSegment();
    }

    public function getName(): string
    {
        return $this->method->getRegisterHandler()->getName();
    }

    public function getDescription(): string
    {
        return $this->method->getRegisterHandler()->getDescription();
    }

    public function getSupportLink(): string
    {
        return $this->method->getRegisterHandler()->getSupportLink();
    }

    public function getThumbnail(): string
    {
        return $this->method->getThumbnail();
    }

    public function getComponent(): string
    {
        return $this->method->getRegisterHandler()->getComponent();
    }

    public function isAvailable(): bool
    {
        return $this->method->getRegisterHandler()->isAvailable();
    }

    public function getUnavailableMessage(): string
    {
        return $this->method->getRegisterHandler()->getUnavailableMessage();
    }

    public function jsonSerialize()
    {
        return [
            'urlSegment' => $this->getURLSegment(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'supportLink' => $this->getSupportLink(),
            'thumbnail' => $this->getThumbnail(),
            'component' => $this->getComponent(),
            'isAvailable' => $this->isAvailable(),
            'unavailableMessage' => $this->isAvailable() ? '' : $this->getUnavailableMessage(),
        ];
    }
}
