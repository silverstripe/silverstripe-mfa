<?php
namespace SilverStripe\MFA\Model;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Method\Handler\LoginHandlerInterface;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

/**
 * @package SilverStripe\MFA\Model
 *
 * @property string MethodClassName
 * @property array Data
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

    /**
     * Accessor for Data field to ensure it's presented as an array instead of a JSON blob
     *
     * @return array
     */
    public function getData()
    {
        return (array) json_decode($this->getField('Data'), true);
    }

    /**
     * Setter for the Data field to ensure it's saved as a JSON blob
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->setField('Data', json_encode($data));
    }

    /**
     * @return MethodInterface
     */
    protected function getMethod()
    {
        if (!$this->method) {
            $this->method = Injector::inst()->create($this->MethodClassName);
        }

        return $this->method;
    }
}
