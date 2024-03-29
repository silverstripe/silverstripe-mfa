<?php

namespace SilverStripe\MFA\Tests\Service;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method;
use SilverStripe\MFA\Tests\Stub\DuplicatedBasicMath;
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

    public function testInvalidMethodsThrowExceptions()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Given method "SilverStripe\Security\Member" does not implement SilverStripe\MFA\Method\MethodInterface'
        );
        Config::modify()->set(MethodRegistry::class, 'methods', [Member::class]);
        $registry = MethodRegistry::singleton();
        $registry->getMethods();
    }

    public function testRegisteringMethodMultipleTimesThrowsException()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Cannot register MFA methods more than once. ' .
            'Check your config: SilverStripe\MFA\Tests\Stub\BasicMath\Method'
        );
        Config::modify()->set(MethodRegistry::class, 'methods', [
            Method::class,
            Method::class,
            Method::class,
        ]);

        MethodRegistry::singleton()->getMethods();
    }

    public function testRegisteringMethodsWithSameURLSegmentThrowsException()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Cannot register multiple MFA methods with the same URL segment: basic-math'
        );
        Config::modify()->set(MethodRegistry::class, 'methods', [
            Method::class,
            DuplicatedBasicMath\Method::class,
        ]);

        MethodRegistry::singleton()->getMethods();
    }
}
