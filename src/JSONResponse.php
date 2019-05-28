<?php declare(strict_types=1);

namespace SilverStripe\MFA;

use SilverStripe\Control\HTTPResponse;

/**
 * Provides a simplified method for creating JSON-based HTTPResponses.
 *
 * @package SilverStripe\MFA
 */
trait JSONResponse
{
    public function jsonResponse(array $body, int $status = 200): HTTPResponse
    {
        return HTTPResponse::create()
            ->setStatusCode($status)
            ->addHeader('Content-Type', 'application/json')
            ->setBody(json_encode($body));
    }
}
