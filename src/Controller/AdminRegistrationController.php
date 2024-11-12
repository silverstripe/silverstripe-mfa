<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Controller;

use Psr\Log\LoggerInterface;
use SilverStripe\Admin\AdminController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\RequestHandler\BaseHandlerTrait;
use SilverStripe\MFA\RequestHandler\RegistrationHandlerTrait;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\State\AvailableMethodDetailsInterface;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Security\SecurityToken;

/**
 * This controller handles actions that a user may perform on MFA methods registered on their own account while logged
 * in. This includes deleting methods, registering new methods and replacing (re-registering) existing methods.
 */
class AdminRegistrationController extends AdminController
{
    use RegistrationHandlerTrait;
    use BaseHandlerTrait;

    private static $url_segment = 'mfa';

    private static $url_handlers = [
        'GET register/$Method' => 'startRegistration',
        'POST register/$Method' => 'finishRegistration',
        'DELETE method/$Method' => 'removeRegisteredMethod',
        'PUT method/$Method/default' => 'setDefaultRegisteredMethod',
    ];

    private static $allowed_actions = [
        'startRegistration',
        'finishRegistration',
        'removeRegisteredMethod',
        'setDefaultRegisteredMethod',
    ];

    private static $required_permission_codes = false;

    private static $dependencies = [
        'Logger' => '%$' . LoggerInterface::class . '.mfa',
    ];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Start a registration for a method on the currently logged in user
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function startRegistration(HTTPRequest $request): HTTPResponse
    {
        // Prevent caching of response
        HTTPCacheControlMiddleware::singleton()->disableCache(true);

        // Create a fresh store from the current logged in user
        $member = Security::getCurrentUser();
        $store = $this->createStore($member);

        if (!$this->getSudoModeService()->check($request->getSession())) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.INVALID_SESSION', 'Invalid session. Please refresh and try again.')]],
                400
            );
        }

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

        if (!$store || !$this->getSudoModeService()->check($request->getSession())) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.INVALID_SESSION', 'Invalid session. Please refresh and try again.')]],
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
     * Set the default registered method for the current user to that provided by the MethodID parameter.
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function setDefaultRegisteredMethod(HTTPRequest $request): HTTPResponse
    {
        // Ensure CSRF and sudo-mode protection
        if (
            !SecurityToken::inst()->checkRequest($request)
            || !$this->getSudoModeService()->check($request->getSession())
        ) {
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

        // Set the method as the default
        /** @var Member&MemberExtension $member */
        $member = Security::getCurrentUser();
        $registeredMethodManager = RegisteredMethodManager::singleton();
        $registeredMethod = $registeredMethodManager->getFromMember($member, $method);
        if (!$registeredMethod) {
            return $this->jsonResponse(
                ['errors' => [_t(__CLASS__ . '.INVALID_REGISTERED_METHOD', 'No such registered method is available')]],
                400
            );
        }
        try {
            $member->setDefaultRegisteredMethod($registeredMethod);
            $member->write();
        } catch (ValidationException $exception) {
            $this->logger->debug(
                'Failed to set default registered method for user #' . $member->ID . ' to ' . $specifiedMethod
                . ': ' . $exception->getMessage()
            );

            return $this->jsonResponse(
                ['errors' => [_t(
                    __CLASS__ . '.COULD_NOT_SET_DEFAULT',
                    'Could not set the default method for the user'
                )]],
                400
            );
        }

        return $this->jsonResponse(['success' => true]);
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

    /**
     * @param LoggerInterface|null $logger
     * @return $this
     */
    public function setLogger(?LoggerInterface $logger): AdminRegistrationController
    {
        $this->logger = $logger;
        return $this;
    }
}
