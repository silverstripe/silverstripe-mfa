<?php

namespace SilverStripe\MFA\Tests\Service;

use Config;
use SapphireTest;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method;
use Member;
use SilverStripe\MFA\Tests\Stub\DuplicatedBasicMath;
use UnexpectedValueException;

class MethodRegistryTest extends SapphireTest
{
    public function testGetAllMethodsReturnsRegisteredMethods()
    {
        Config::inst()->remove(MethodRegistry::class, 'methods');
        Config::inst()->update(MethodRegistry::class, 'methods', [Method::class]);
        $registry = MethodRegistry::singleton();
        $methods = $registry->getMethods();

        $this->assertCount(1, $methods);
        $this->assertInstanceOf(Method::class, reset($methods));
    }

    /**
     * @expectedException UnexpectedValueException
     * phpcs:disable
     * @expectedExceptionMessage Given method "Member" does not implement SilverStripe\MFA\Method\MethodInterface
     * phpcs:enable
     */
    public function testInvalidMethodsThrowExceptions()
    {
        Config::inst()->remove(MethodRegistry::class, 'methods');
        Config::inst()->update(MethodRegistry::class, 'methods', [Member::class]);
        $registry = MethodRegistry::singleton();
        $registry->getMethods();
    }

    /**
     * @expectedException UnexpectedValueException
     * phpcs:disable
     * @expectedExceptionMessage Cannot register MFA methods more than once. Check your config: SilverStripe\MFA\Tests\Stub\BasicMath\Method
     * phpcs:enable
     */
    public function testRegisteringMethodMultipleTimesThrowsException()
    {
        Config::inst()->remove(MethodRegistry::class, 'methods');
        Config::inst()->update(MethodRegistry::class, 'methods', [
            Method::class,
            Method::class,
            Method::class,
        ]);

        MethodRegistry::singleton()->getMethods();
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Cannot register multiple MFA methods with the same URL segment: basic-math
     */
    public function testRegisteringMethodsWithSameURLSegmentThrowsException()
    {
        Config::inst()->remove(MethodRegistry::class, 'methods');
        Config::inst()->update(MethodRegistry::class, 'methods', [
            Method::class,
            DuplicatedBasicMath\Method::class,
        ]);

        MethodRegistry::singleton()->getMethods();
    }
}
