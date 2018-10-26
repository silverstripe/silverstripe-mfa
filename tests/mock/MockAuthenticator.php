<?php

namespace Firesphere\BootstrapMFA\Tests\Mocks;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Forms\BootstrapMFALoginForm;
use Firesphere\BootstrapMFA\Handlers\BootstrapMFALoginHandler;
use Firesphere\BootstrapMFA\Interfaces\MFAAuthenticator;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ValidationResult;

class MockAuthenticator extends BootstrapMFAAuthenticator implements TestOnly, MFAAuthenticator
{

    /**
     * Get the MFA form
     *
     * @param BootstrapMFALoginHandler $controller
     * @param string $name
     * @return BootstrapMFALoginForm
     */
    public function getMFAForm($controller, $name)
    {
        $fields = FieldList::create([
            TextField::create('token', 'Token')
        ]);
        $actions = FieldList::create([
            FormAction::create('verifyMFA', 'Verify')
        ]);

        $form = BootstrapMFALoginForm::create($controller, $name, $fields, $actions);
        $form->setAuthenticatorClass(self::class);

        return $form;
    }

    /**
     * Verify the MFA code
     *
     * @param array $data
     * @param HTTPRequest $request
     * @param string $token
     * @param ValidationResult $result
     * @return mixed
     */
    public function verifyMFA($data, $request, $token, &$result)
    {
        // TODO: Implement verifyMFA() method.
    }

    public function getTokenField()
    {
        return 'token';
    }
}
