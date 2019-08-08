<?php

namespace SilverStripe\MFA\Tests\Report;

use Config;
use DropdownField;
use SapphireTest;
use SilverStripe\MFA\BackupCode\Method as BackupMethod;
use EnabledMembers;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SilverStripe\MFA\Tests\Stub\Null\Method as NullMethod;

class EnabledMembersTest extends SapphireTest
{
    protected static $fixture_file = 'EnabledMembersTest.yml';

    public function setUp()
    {
        parent::setUp();

        Config::inst()->update(MethodRegistry::class, 'default_backup_method', BackupMethod::class);
        Config::inst()->remove(MethodRegistry::class, 'methods');
        Config::inst()->update(
            MethodRegistry::class,
            'methods',
            [BasicMathMethod::class, NullMethod::class, BackupMethod::class]
        );
    }

    public function testRegisteredMethodFilterFieldDoesNotContainBackupMethod()
    {
        $report = new EnabledMembers();
        $fields = $report->parameterFields();
        /** @var DropdownField $registeredMethodFilterField */
        $registeredMethodFilterField = $fields->dataFieldByName('Methods');
        $source = $registeredMethodFilterField->getSource();
        $this->assertArrayNotHasKey(BackupMethod::class, $source);
    }

    /**
     * @dataProvider sourceRecordsParamsProvider
     * @param array $params
     * @param int $expectedRows
     * @param string $explanation
     */
    public function testSourceRecords($params, $expectedRows, $explanation = null)
    {
        $report = new EnabledMembers();
        $records = $report->sourceRecords($params);
        $this->assertEquals($expectedRows, $records->count(), $explanation);
    }

    public function sourceRecordsParamsProvider()
    {
        return [
            [[], 5],
            [['Skipped' => '1'], 2, 'Skipped setup filter works'],
            [
                ['Member' => 'i'],
                4,
                'Searching for a member works over FirstName or Surname and is a disjunctive partial match'
            ],
            [['Member' => '.com'], 4, 'Member search includes Email'],
            [['Methods' => BasicMathMethod::class], 2, 'Searching for a particular method works'],
            [
                ['Methods' => BasicMathMethod::class, 'Member' => 'EDITOR', 'Skipped' => '1'],
                1,
                'Control test that all filters are conjunctive'
            ],
            [['Methods' => BasicMathMethod::class, 'Member' => 'EDITOR', 'Skipped' => '0'], 0],
            [['Methods' => BasicMathMethod::class, 'Member' => 'EDITOR', 'Skipped' => '1'], 0],
            [['Methods' => BasicMathMethod::class, 'Member' => 'MFA', 'Skipped' => '1'], 0],
            [['Methods' => NullMethod::class, 'Member' => 'EDITOR', 'Skipped' => '1'], 0],
            [['Methods' => NullMethod::class, 'Member' => 'MFA', 'Skipped' => '0'], 1],
            [
                ['Methods' => '', 'Member' => '', 'Skipped' => ''],
                5,
                'An empty filter input set does not destroy the result set'
            ],
        ];
    }
}
