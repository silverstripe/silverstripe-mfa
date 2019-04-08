<?php

namespace SilverStripe\MFA\State;

use JsonSerializable;

/**
 * Used to provide details about an available {@link \SilverStripe\MFA\Method\MethodInterface} instance, for example
 * when being used in the multi factor application schema.
 */
interface AvailableMethodDetailsInterface extends JsonSerializable
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getURLSegment(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return string
     */
    public function getSupportLink(): string;

    /**
     * @return string
     */
    public function getThumbnail(): string;

    /**
     * @return string
     */
    public function getComponent(): string;

    /**
     * Whether the method can be used for registration.
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * A message to display on the frontend for the method when it is not available to be used.
     *
     * @return string
     */
    public function getUnavailableMessage(): string;
}
