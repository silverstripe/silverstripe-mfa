<?php

namespace SilverStripe\MFA\Tests\Store;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Store\SessionStore;
use SilverStripe\Security\Member;

class SessionStoreTest extends SapphireTest
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /possibly incorrectly encoded/
     */
    public function testSerializeThrowsExceptionOnFailure()
    {
        $store = new SessionStore($this->createMock(Member::class));
        $store->setState(['some binary' => random_bytes(32)]);
        $store->serialize();
    }

    public function testSetState()
    {
        $store = new SessionStore($this->createMock(Member::class));
        $store->setState(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $store->getState());
    }

    public function testAddState()
    {
        $store = new SessionStore($this->createMock(Member::class));
        $store->setState(['foo' => 'bar', 'bar' => 'baz']);
        $store->addState(['foo' => 'baz']);
        $this->assertSame(['foo' => 'baz', 'bar' => 'baz'], $store->getState());
    }

    /**
     * @expectedException \SilverStripe\MFA\Exception\InvalidMethodException
     * @expectedExceptionMessage You cannot verify with a method you have already verified
     */
    public function testSetMethodWithVerifiedMethod()
    {
        $store = new SessionStore($this->createMock(Member::class));
        $store->addVerifiedMethod('foobar');
        $store->setMethod('foobar');
    }

    public function testSetMethod()
    {
        $store = new SessionStore($this->createMock(Member::class));
        $store->setMethod('foobar');
        $this->assertSame('foobar', $store->getMethod());
    }

    public function testSetMemberDoesNotResetMethodsWhenNoChange()
    {
        $member1 = $this->createMock(Member::class);

        $store = new SessionStore($member1);
        $store->setMethod('foobar');
        $store->addVerifiedMethod('foobar');
        $store->setMember($member1);

        $this->assertSame('foobar', $store->getMethod());
    }

    public function testSetMemberResetsMethodsWhenMemberChanged()
    {
        $member1 = new Member();
        $member1->ID = 25;
        $member2 = new Member();
        $member2->ID = 50;

        $store = new SessionStore($member1);
        $store->setMethod('foobar');
        $store->addVerifiedMethod('foobar');
        $store->setMember($member2);

        $this->assertEmpty($store->getMethod());
    }
}
