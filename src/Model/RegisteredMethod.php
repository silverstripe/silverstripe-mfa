<?php
namespace SilverStripe\MFA\Model;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Method\Handler\LoginHandlerInterface;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\State\RegisteredMethodDetailsInterface;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

/**
 * @package SilverStripe\MFA\Model
 *
 * @property int ID
 * @property string MethodClassName
 * @property string Data
 */
class RegisteredMethod extends DataObject
{
    private static $table_name = 'MFARegisteredMethod';

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
    public function getMethod()
    {
        if (!$this->method) {
            $this->method = Injector::inst()->create($this->MethodClassName);
        }
        return $this->method;
    }

    /**
     * @return LoginHandlerInterface
     */
    public function getLoginHandler()
    {
        return $this->getMethod()->getLoginHandler();
    }

    /**
     * @return RegisterHandlerInterface
     */
    public function getRegisterHandler()
    {
        return $this->getMethod()->getRegisterHandler();
    }

    public function getDetails()
    {
        return Injector::inst()->create(RegisteredMethodDetailsInterface::class, $this->getMethod());
    }
}
