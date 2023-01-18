<?php

namespace SilverStripe\MFA\Tests\Store;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Store\SessionStore;
use SilverStripe\ORM\Connect\Database;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Member;

class SessionStoreTest extends SapphireTest
{
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

    public function testSetMethodWithVerifiedMethod()
    {
        $this->expectException(\SilverStripe\MFA\Exception\InvalidMethodException::class);
        $this->expectExceptionMessage('You cannot verify with a method you have already verified');
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

    public function testDatabaseIsNotAccessedOnDeserialise()
    {
        // Create a store
        $member = new Member();
        $member->ID = 1;
        $store = new SessionStore($member);
        $serialised = $store->__serialize();

        // Replace the DB connection with a mock
        $connection = DB::get_conn();
        $database = $this->getMockBuilder(Database::class)
            ->enableProxyingToOriginalMethods()
            ->setProxyTarget($connection)
            ->getMock();

        $database->expects($this->never())->method('query');
        $database->expects($this->never())->method('preparedQuery');
        DB::set_conn($database);

        // Replicate the deserialisation that happens on session start
        $store->__unserialize($serialised);

        // Finish the test and allow mock assertions to fail the test
        DB::set_conn($connection);
    }
}
