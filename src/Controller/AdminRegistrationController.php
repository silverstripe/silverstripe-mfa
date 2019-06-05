<?php declare(strict_types=1);

namespace SilverStripe\MFA\Controller;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\RequestHandler\BaseHandlerTrait;
use SilverStripe\MFA\RequestHandler\RegistrationHandlerTrait;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Service\SchemaGenerator;
use SilverStripe\MFA\State\AvailableMethodDetailsInterface;
use SilverStripe\MFA\State\RegisteredMethodDetailsInterface;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Security\SecurityToken;

/**
 * This controller handles actions that a user may perform on MFA methods registered on their own account while logged
 * in. This includes deleting methods, registering new methods and replacing (re-registering) existing methods.
 */
class AdminRegistrationController extends LeftAndMain
{
    use RegistrationHandlerTrait;
    use BaseHandlerTrait;

    private static $url_segment = 'mfa';

    private static $ignore_menuitem = true;

    private static $url_handlers = [
        'GET register/$Method' => 'startRegistration',
        'POST register/$Method' => 'finishRegistration',
        'GET remove/$Method' => 'removeRegisteredMethod',
    ];

    private static $allowed_actions = [
        'startRegistration',
        'finishRegistration',
        'removeRegisteredMethod',
    ];

    /**
     * Start a registration for a method on the currently logged in user
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
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

        $response = $this->createStartRegistrationResponse($store, $method, true);
        $store->save($request);

        return $response;
    }

    /**
     * Complete a registration for a method for the currently logged in user
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
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

        return $this->jsonResponse([
            'success' => true,
            'method' => $result->getContext()['registeredMethod'] ?? null,
        ], 201);
    }

    /**
     * Remove the specified method from the currently logged in user
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function removeRegisteredMethod(HTTPRequest $request): HTTPResponse
    {
        // Ensure CSRF protection
        if (!SecurityToken::inst()->checkRequest($request)) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.CSRF_FAILURE', 'Request timed out, please try again')]],
                400
            );
        }

        // Get the specified method
        $methodRegistry = MethodRegistry::singleton();
        $specifiedMethod = $request->param('Method');

        if (!$specifiedMethod || !($method = $methodRegistry->getMethodByURLSegment($specifiedMethod))) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.INVALID_METHOD', 'No such method is available')]],
                400
            );
        }

        // Remove the method from the user
        $member = Security::getCurrentUser();
        $registeredMethodManager = RegisteredMethodManager::singleton();
        $result = $registeredMethodManager->deleteFromMember($member, $method);

        if (!$result) {
            return $this->jsonResponse(
                ['errors' => [_t(
                    __CLASS__ . '.COULD_NOT_DELETE',
                    'Could not delete the specified method from the user'
                )]],
                400
            );
        }

        $backupMethod = $methodRegistry->getBackupMethod();
        return $this->jsonResponse([
            'success' => true,
            'availableMethod' => Injector::inst()->create(AvailableMethodDetailsInterface::class, $method),
            // Indicate if the user has a backup method registered to keep the UI up to date
            // Deleting methods may remove the backup method if there are no other methods remaining.
            'hasBackupMethod' => $backupMethod && $registeredMethodManager->getFromMember(
                $member,
                $backupMethod
            ),
        ]);
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
