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
    public function getName();

    /**
     * @return string
     */
    public function getURLSegment();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getSupportLink();

    /**
     * @return string
     */
    public function getComponent();
}
