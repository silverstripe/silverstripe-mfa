<?php

namespace SilverStripe\MFA\Tests\Report;

use Config;
use DropdownField;
use EnabledMembers;
use SapphireTest;
use SilverStripe\MFA\BackupCode\Method as BackupMethod;
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
     */
    public function testSourceRecords($params, $expectedRows)
    {
        $report = new EnabledMembers();
        $records = $report->sourceRecords($params);
        $this->assertCount($expectedRows, $records);
    }

    public function sourceRecordsParamsProvider()
    {
        return [
            'no filters' => [[], 5],
            'skipped registration' => [['Skipped' => 'yes'], 2],
            'partial match on member name fields' => [['Member' => 'i'], 4],
            'includes member email field' => [['Member' => '.com'], 4],
            'specific registered method' => [['Methods' => BasicMathMethod::class], 2],
            'method, member name, and skipped filters' => [
                ['Methods' => BasicMathMethod::class, 'Member' => 'EDITOR', 'Skipped' => 'yes'],
                1,
            ],
            'skipped filter removes record that would otherwise match' => [
                ['Methods' => BasicMathMethod::class, 'Member' => 'EDITOR', 'Skipped' => 'no'],
                0,
            ],
            'miss match on method, MFA in name, skipped' => [
                ['Methods' => BasicMathMethod::class, 'Member' => 'MFA', 'Skipped' => 'yes'],
                0,
            ],
            'miss match on method, editor in name, skipped' => [
                ['Methods' => NullMethod::class, 'Member' => 'EDITOR', 'Skipped' => 'yes'],
                0,
            ],
            'hit match on method, MFA in name, not skipped' => [
                ['Methods' => NullMethod::class, 'Member' => 'MFA', 'Skipped' => 'no'],
                1,
            ],
            'empty filters returns all records' => [
                ['Methods' => '', 'Member' => '', 'Skipped' => ''],
                5,
            ],
            '"none" filter on method names' => [
                ['Methods' => 'none'],
                3,
            ],
            '"any" filter on method names' => [
                ['Methods' => 'any'],
                3,
            ],
        ];
    }
}
