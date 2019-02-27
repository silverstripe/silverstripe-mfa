<?php
namespace SilverStripe\MFA\Model;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\AuthenticationMethod\AuthenticatorInterface;
use SilverStripe\MFA\AuthenticationMethodInterface;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

/**
 * @package SilverStripe\MFA\Model
 *
 * @property string MethodClassName
 * @property array Data
 */
class AuthenticationMethod extends DataObject
{
    private static $table_name = 'MFAAuthenticationMethod';

    private static $db = [
        // The class name of the AuthenticationMethodInterface that this record refers to
        'MethodClassName' => 'Varchar',
        // Data stored as a JSON blob that may contain detail specific to this registration of the authenticator
        'Data' => 'Text',
    ];

    private static $has_one = [
        'Member' => Member::class,
    ];

    /**
     * @var AuthenticationMethodInterface
     */
    protected $method;

    /**
     * @return AuthenticatorInterface
     */
    public function getAuthenticator()
    {
        return $this->getMethod()->getAuthenticator();
    }

    /**
     * @return mixed
     */
    public function getRegistrar()
    {
        return $this->getMethod()->getRegistrar();
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
     * @return AuthenticationMethodInterface
     */
    protected function getMethod()
    {
        if (!$this->method) {
            $this->method = Injector::inst()->create($this->MethodClassName);
        }

        return $this->method;
    }
}
