<?php

namespace SilverStripe\MFA\Exception;

use RuntimeException;

/**
 * Represents a failure to correctly hash a multi factory authentication code
 */
class HashFailedException extends RuntimeException
{

}
