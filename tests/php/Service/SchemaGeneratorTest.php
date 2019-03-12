<?php

namespace SilverStripe\MFA\Tests\Service;

use PHPUnit_Framework_MockObject_MockObject;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\SchemaGenerator;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;

class SchemaGeneratorTest extends SapphireTest
{
    protected static $fixture_file = 'SchemaGeneratorTest.yml';

    /**
     * @var HTTPRequest|PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var StoreInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    protected function setUp()
    {
        parent::setUp();

        $this->request = $this->createMock(HTTPRequest::class);
        $this->store = $this->createMock(StoreInterface::class);

        MethodRegistry::config()->set('methods', [
            BasicMathMethod::class,
        ]);
    }

    /**
     * @expectedException \SilverStripe\MFA\Exception\MemberNotFoundException
     */
    public function testGetMemberThrowsExceptionWithoutMember()
    {
        $this->logOut();
        $generator = new SchemaGenerator($this->request, $this->store);
        $generator->getMember();
    }

    public function testGetSchema()
    {
        $this->logInAs('sally_smith');

        $generator = new SchemaGenerator($this->request, $this->store);
        $schema = $generator->getSchema();

        $this->assertArrayHasKey('registeredMethods', $schema);
        $this->assertNotEmpty($schema['registeredMethods']);
        $this->assertArrayHasKey('availableMethods', $schema);
        $this->assertNotEmpty($schema['availableMethods']);
        $this->assertArrayHasKey('defaultMethod', $schema);
        $this->assertNotEmpty($schema['defaultMethod']);

        $this->assertSame('backup-codes', $schema['registeredMethods'][0]['urlSegment']);
        $this->assertSame('basic-math', $schema['availableMethods'][0]['urlSegment']);
        $this->assertSame('backup-codes', $schema['defaultMethod']);
    }
}
