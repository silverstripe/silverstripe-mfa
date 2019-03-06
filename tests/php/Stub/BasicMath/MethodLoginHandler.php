<?php
namespace SilverStripe\MFA\Tests\Stub\BasicMath;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Dev\TestOnly;
use SilverStripe\MFA\Method\Handler\LoginHandlerInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Store\StoreInterface;

/**
 * Handles login attempts for the Math Method
 */
class MethodLoginHandler implements LoginHandlerInterface, TestOnly
{
    use Configurable;

    private static $number_of_numbers = 2;

    /**
     * Prepare this authentication method to verify a member by initialising state in session and generating details to
     * provide to a frontend React component
     *
     * @param StoreInterface $store An object that hold session data (and the Member) that can be mutated
     * @return array Props to be passed to a front-end React component
     */
    public function start(StoreInterface $store, RegisteredMethod $registeredMethod)
    {
        $numbers = [];

        for ($i = 0; $i < static::config()->get('number_of_numbers'); $i++) {
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
     * @return bool
     */
    public function verify(HTTPRequest $request, StoreInterface $store, RegisteredMethod $registeredMethod)
    {
        $state = $store->getState();
        return hash_equals((string)$state['answer'], (string)$request->param('answer'));
    }

    /**
     * Provide a string (possibly passed through i18n) that serves as a lead in for choosing this option for
     * authentication
     *
     * eg. "Enter one of your recovery codes"
     *
     * @return string
     */
    public function getLeadInLabel()
    {
        return 'Verify by solving a complex math problem';
    }
}
