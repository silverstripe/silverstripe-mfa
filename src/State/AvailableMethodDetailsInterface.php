<?php

namespace SilverStripe\MFA\State;

use JsonSerializable;

/**
 * Used to provide details about an available {@link \SilverStripe\MFA\Method\MethodInterface} instance, for example
 * when being used in the multi-factor application schema.
 */
interface AvailableMethodDetailsInterface extends JsonSerializable
{

}
