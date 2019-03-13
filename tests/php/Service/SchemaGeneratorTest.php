<?php

namespace SilverStripe\MFA\Tests\Service;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\SchemaGenerator;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SilverStripe\Security\Member;

class SchemaGeneratorTest extends SapphireTest
{
    protected static $fixture_file = 'SchemaGeneratorTest.yml';

    /**
     * @var SchemaGenerator
     */
    protected $generator;

    protected function setUp()
    {
        parent::setUp();

        MethodRegistry::config()->set('methods', [
            BasicMathMethod::class,
        ]);

        $this->generator = new SchemaGenerator();
    }

    public function testGetSchema()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $this->logInAs($member);

        $schema = $this->generator->getSchema($member);

        $this->assertArrayHasKey('registeredMethods', $schema);
        $this->assertNotEmpty($schema['registeredMethods']);
        $this->assertSame('backup-codes', $schema['registeredMethods'][0]['urlSegment']);

        $this->assertArrayHasKey('availableMethods', $schema);
        $this->assertNotEmpty($schema['availableMethods']);
        $this->assertSame('basic-math', $schema['availableMethods'][0]['urlSegment']);

        $this->assertArrayHasKey('defaultMethod', $schema);
        $this->assertNotEmpty($schema['defaultMethod']);
        $this->assertSame('backup-codes', $schema['defaultMethod']);

        $this->assertArrayHasKey('canSkip', $schema);
        $this->assertArrayHasKey('shouldRedirect', $schema);
    }
}
