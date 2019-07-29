<?php

namespace SilverStripe\MFA\Tests\Store;

use DB;
use SapphireTest;
use SilverStripe\MFA\Store\SessionStore;
use Member;
use SS_Database;

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

    public function testDatabaseIsNotAccessedOnDeserialise()
    {
        // Create a store
        $member = new Member();
        $member->ID = 1;
        $store = new SessionStore($member);
        $serialised = $store->serialize();

        // Replace the DB connection with a mock
        $connection = DB::get_conn();
        $database = $this->getMockBuilder(SS_Database::class)
            ->enableProxyingToOriginalMethods()
            ->setProxyTarget($connection)
            ->getMock();

        $database->expects($this->never())->method('query');
        $database->expects($this->never())->method('preparedQuery');
        DB::set_conn($database);

        // Replicate the deserialisation that happens on session start
        $store->unserialize($serialised);

        // Finish the test and allow mock assertions to fail the test
        DB::set_conn($connection);
    }
}
