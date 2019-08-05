<?php

namespace SilverStripe\MFA\Tests\Report;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\DropdownField;
use SilverStripe\MFA\BackupCode\Method as BackupMethod;
use SilverStripe\MFA\Report\EnabledMembers;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SilverStripe\MFA\Tests\Stub\Null\Method as NullMethod;

class EnabledMembersTest extends SapphireTest
{
    protected static $fixture_file = 'EnabledMembersTest.yml';

    protected function setUp()
    {
        parent::setUp();
        Config::modify()
            ->set(MethodRegistry::class, 'default_backup_method', BackupMethod::class)
            ->set(MethodRegistry::class, 'methods', [BasicMathMethod::class, NullMethod::class, BackupMethod::class]);
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
            [[], 5, 'Sanity test'],
            [['Skipped' => '1'], 2, 'Skipped setup filter works'],
            [['Skipped' => '1', 'Count' => '0'], 1, 'Show members that have skipped and do not have a method set up'],
            [['Count' => '2'], 1, 'Filtering by number of methods set up works'],
            [['Count' => '2', 'Methods' => BasicMathMethod::class], 1, 'Count and Methods fitlers work together'],
            [
                ['Member' => 'i'],
                4,
                'Searching for a member works over FirstName or Surname and is a disjunctive partial match'
            ],
            [['Member' => '.com'], 4, 'Member search includes Email'],
            [['Methods' => BasicMathMethod::class], 2, 'Searching for a particular method works'],
            [
                ['Methods' => BasicMathMethod::class, 'Member' => 'EDITOR', 'Count' => '1', 'Skipped' => '1'],
                1,
                'Control test that all filters are conjunctive'
            ],
            [['Methods' => BasicMathMethod::class, 'Member' => 'EDITOR', 'Count' => '1', 'Skipped' => '0'], 0],
            [['Methods' => BasicMathMethod::class, 'Member' => 'EDITOR', 'Count' => '2', 'Skipped' => '1'], 0],
            [['Methods' => BasicMathMethod::class, 'Member' => 'MFA', 'Count' => '1', 'Skipped' => '1'], 0],
            [['Methods' => NullMethod::class, 'Member' => 'EDITOR', 'Count' => '1', 'Skipped' => '1'], 0],
            [['Methods' => NullMethod::class, 'Member' => 'MFA', 'Count' => '2', 'Skipped' => '0'], 1],
            [
                ['Methods' => '', 'Member' => '', 'Count' => '', 'Skipped' => ''],
                5,
                'An empty filter input set does not destroy the result set'
            ],
        ];
    }
}
