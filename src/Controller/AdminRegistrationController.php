<?php declare(strict_types=1);

namespace SilverStripe\MFA\Controller;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\MFA\RequestHandler\BaseHandlerTrait;
use SilverStripe\MFA\RequestHandler\RegistrationHandlerTrait;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\Security\Security;

class AdminRegistrationController extends LeftAndMain
{
    use RegistrationHandlerTrait;
    use BaseHandlerTrait;

    private static $url_segment = 'mfa';

    private static $url_handlers = [
        'GET register/$Method' => 'startRegistration',
        'POST register/$Method' => 'finishRegistration',
    ];

    private static $allowed_actions = [
        'startRegistration',
        'finishRegistration',
    ];

    public function startRegistration(HTTPRequest $request): HTTPResponse
    {
        // Create a fresh store from the current logged in user
        $member = Security::getCurrentUser();
        $store = $this->createStore($member);

        // Get the specified method
        $method = MethodRegistry::singleton()->getMethodByURLSegment($request->param('Method'));

        if (!$method) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.INVALID_METHOD', 'No such method is available')]],
                400
            );
        }

        $response = $this->createStartRegistrationResponse($store, $method);
        $store->save($request);

        return $response;
    }

    public function finishRegistration(HTTPRequest $request): HTTPResponse
    {
        $store = $this->getStore();

        if (!$store) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.INVALID_SESSION', 'Invalid session. Please try again')]],
                400
            );
        }

        $method = MethodRegistry::singleton()->getMethodByURLSegment($request->param('Method'));

        if (!$method) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.INVALID_METHOD', 'No such method is available')]],
                400
            );
        }

        $result = $this->completeRegistrationRequest($store, $method, $request);

        if (!$result->isSuccessful()) {
            return $this->jsonResponse(['errors' => [$result->getMessage()]], 400);
        }

        $store::clear($request);

        return $this->jsonResponse(['success' => true], 201);
    }

    /**
     * Respond with the given array as a JSON response
     *
     * @param array $response
     * @param int $code The HTTP response code to set on the response
     * @return HTTPResponse
     */
    protected function jsonResponse(array $response, int $code = 200): HTTPResponse
    {
        return HTTPResponse::create(json_encode($response))
            ->addHeader('Content-Type', 'application/json')
            ->setStatusCode($code);
    }
}
