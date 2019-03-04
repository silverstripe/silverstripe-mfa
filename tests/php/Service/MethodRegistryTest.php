<?php

namespace SilverStripe\MFA\Tests\Service;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method;
use SilverStripe\Security\Member;
use UnexpectedValueException;

class MethodRegistryTest extends SapphireTest
{
    public function testGetAllMethodsReturnsRegisteredMethods()
    {
        Config::modify()->set(MethodRegistry::class, 'methods', [Method::class]);
        $registry = MethodRegistry::singleton();
        $methods = $registry->getMethods();

        $this->assertCount(1, $methods);
        $this->assertInstanceOf(Method::class, reset($methods));
    }

    /**
     * phpcs:disable
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Given method "SilverStripe\Security\Member" does not implement SilverStripe\MFA\Method\MethodInterface
     * phpcs:enable
     */
    public function testInvalidMethodsThrowExceptions()
    {
        Config::modify()->set(MethodRegistry::class, 'methods', [Member::class]);
        $registry = MethodRegistry::singleton();
        $registry->getMethods();
    }
}
