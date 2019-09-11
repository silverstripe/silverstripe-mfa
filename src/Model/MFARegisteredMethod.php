<?php

declare(strict_types=1);

use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\Handler\VerifyHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
/**
 * Known as RegisteredMethod in SS4, but renamed and un-namespaced for table
 * name consistency in SS3.
 *
 * @property int ID
 * @property string MethodClassName
 * @property string Data
 * @method Member Member
 */
class MFARegisteredMethod extends DataObject
{
    private static $db = [
        // The class name of the MethodInterface that this record refers to
        'MethodClassName' => 'Varchar',
        // Data stored as a JSON blob that may contain detail specific to this registration of the authenticator
        'Data' => 'Text',
    ];

    private static $has_one = [
        'Member' => Member::class,
    ];

    /**
     * @var MethodInterface
     */
    protected $method;

    /**
     * @return MethodInterface
     */
    public function getMethod(): MethodInterface
    {
        if (!$this->method) {
            $this->method = Injector::inst()->create($this->MethodClassName);
        }
        return $this->method;
    }

    /**
     * @return VerifyHandlerInterface
     */
    public function getVerifyHandler(): VerifyHandlerInterface
    {
        return $this->getMethod()->getVerifyHandler();
    }

    /**
     * @return RegisterHandlerInterface
     */
    public function getRegisterHandler(): RegisterHandlerInterface
    {
        return $this->getMethod()->getRegisterHandler();
    }
}
