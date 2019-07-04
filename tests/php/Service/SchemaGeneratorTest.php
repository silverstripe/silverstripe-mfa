<?php

namespace SilverStripe\MFA\Tests\Service;

use SapphireTest;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\SchemaGenerator;
use SilverStripe\MFA\State\RegisteredMethodDetailsInterface;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use Member;

class SchemaGeneratorTest extends SapphireTest
{
    protected static $fixture_file = 'SchemaGeneratorTest.yml';

    /**
     * @var SchemaGenerator
     */
    protected $generator;

    public function setUp()
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
        $member->logIn();

        $schema = $this->generator->getSchema($member);

        $this->assertArrayHasKey('registeredMethods', $schema);
        $this->assertContainsOnlyInstancesOf(RegisteredMethodDetailsInterface::class, $schema['registeredMethods']);
        $this->assertSame('backup-codes', $schema['registeredMethods'][0]->jsonSerialize()['urlSegment']);

        $this->assertArrayHasKey('availableMethods', $schema);
        $this->assertContainsOnlyInstancesOf(RegisteredMethodDetailsInterface::class, $schema['registeredMethods']);
        $this->assertSame('basic-math', $schema['availableMethods'][0]->jsonSerialize()['urlSegment']);

        $this->assertArrayHasKey('allMethods', $schema);
        $this->assertCount(1, $schema['allMethods'], 'Only BasicMath is registered; allMethods was wrong');

        $this->assertArrayHasKey('defaultMethod', $schema);
        $this->assertNotEmpty($schema['defaultMethod']);
        $this->assertSame('backup-codes', $schema['defaultMethod']);

        $this->assertArrayHasKey('canSkip', $schema);
        $this->assertArrayHasKey('shouldRedirect', $schema);
    }
}
