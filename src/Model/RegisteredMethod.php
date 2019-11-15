<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Model;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\Handler\VerifyHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

/**
 * @package SilverStripe\MFA\Model
 *
 * @property int ID
 * @property string MethodClassName
 * @property string Data
 * @method Member Member
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
