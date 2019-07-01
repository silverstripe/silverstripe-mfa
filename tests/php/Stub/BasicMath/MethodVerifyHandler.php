<?php declare(strict_types=1);

namespace SilverStripe\MFA\Tests\Stub\BasicMath;

use SS_HTTPRequest as HTTPRequest;
use SS_Object;
use TestOnly;
use SilverStripe\MFA\Method\Handler\VerifyHandlerInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\State\Result;
use SilverStripe\MFA\Store\StoreInterface;

/**
 * Handles login attempts for the Math Method
 */
class MethodVerifyHandler extends SS_Object implements VerifyHandlerInterface, TestOnly
{
    private static $number_of_numbers = 2;

    /**
     * Prepare this authentication method to verify a member by initialising state in session and generating details to
     * provide to a frontend React component
     *
     * @param StoreInterface $store An object that hold session data (and the Member) that can be mutated
     * @return array Props to be passed to a front-end React component
     */
    public function start(StoreInterface $store, RegisteredMethod $registeredMethod): array
    {
        $numbers = [];

        $numberOfNumbers = $this->config()->get('number_of_numbers') ?: 2;

        for ($i = 0; $i < $numberOfNumbers; $i++) {
            $numbers[] = rand(1, 9);
        }

        $store->setState([
            'answer' => array_sum($numbers),
        ]);

        return [
            'numbers' => $numbers,
        ];
    }

    /**
     * Verify the request has provided the right information to verify the member that aligns with any sessions state
     * that may have been set prior
     *
     * @param HTTPRequest $request
     * @param StoreInterface $store
     * @return Result
     */
    public function verify(HTTPRequest $request, StoreInterface $store, RegisteredMethod $registeredMethod): Result
    {
        $body = json_decode($request->getBody(), true);

        if (!$body['answer']) {
            return Result::create(false, 'Answer was missing');
        }

        $state = $store->getState();
        $hashComparison = hash_equals((string)$state['answer'], (string)$body['answer']);
        if (!$hashComparison) {
            return Result::create(false, 'Answer was wrong');
        }
        return Result::create();
    }

    /**
     * Provide a string (possibly passed through i18n) that serves as a lead in for choosing this option for
     * authentication
     *
     * eg. "Enter one of your recovery codes"
     *
     * @return string
     */
    public function getLeadInLabel(): string
    {
        return 'Verify by solving a complex math problem';
    }

    /**
     * Get the key that a React UI component is registered under (with @silverstripe/react-injector on the front-end)
     *
     * @return string
     */
    public function getComponent(): string
    {
        // This component does not exist
        return 'BasicMathLogin';
    }
}
