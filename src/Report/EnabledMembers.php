<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Report;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\Reports\Report;
use SilverStripe\Security\Member;

if (!class_exists(Report::class)) {
    return;
}

class EnabledMembers extends Report
{
    protected $title = 'Multi-factor authentication status of members';

    protected $description = 'Identifies all members on this site, with columns added to indicate the status each has'
        . ' in regards to multi-factor authentication method registration.';

    protected $dataClass = Member::class;

    /**
     * Cached registered methods fetched for the current list of members. This should only be populated at render time
     * as methods will be fetched for the current "records" on the report
     *
     * @var ArrayList<RegisteredMethod>|null
     */
    private $registeredMethods = null;

    public function title(): string
    {
        return _t(__CLASS__ . '.TITLE', parent::title());
    }

    public function description(): string
    {
        return _t(__CLASS__ . '.DESCRIPTION', parent::description());
    }

    /**
     * Supplies the list displayed in the report
     *
     * @param array $params
     * @return DataList<Member>
     */
    public function sourceRecords($params): DataList
    {
        $sourceList = Member::get();

        // Reports treat 0 as empty, which is interpreted as "any", so we switch it to "no" and "yes" for filtering
        if (!empty($params['Skipped'])) {
            $sourceList = $sourceList->filter('HasSkippedMFARegistration', $params['Skipped'] === 'no' ? 0 : 1);
        }

        // Allow partial matching on standard member fields
        if (!empty($params['Member'])) {
            $sourceList = $sourceList->filterAny([
                'FirstName:PartialMatch' => $params['Member'],
                'Surname:PartialMatch' => $params['Member'],
                'Email:PartialMatch' => $params['Member'],
            ]);
        }

        // Apply "none", "any", or a specific registered method filter
        if (!empty($params['Methods'])) {
            if ($params['Methods'] === 'none') {
                $sourceList = $sourceList->filter('RegisteredMFAMethods.MethodClassName', null);
            } elseif ($params['Methods'] === 'any') {
                $sourceList = $sourceList->filter('RegisteredMFAMethods.MethodClassName:not', null);
            } else {
                $sourceList = $sourceList->filter('RegisteredMFAMethods.MethodClassName', $params['Methods']);
            }
        }

        $this->extend('updateSourceRecords', $sourceList);

        return $sourceList;
    }

    /**
     * List the columns configured to display in the resulting reports GridField
     *
     * @return array
     */
    public function columns(): array
    {
        $columns = singleton(Member::class)->summaryFields() + [
            'RegisteredMethodNames' => [
                'title' => _t(__CLASS__ . '.COLUMN_METHODS_REGISTERED', 'Registered methods')
            ],
            'DefaultRegisteredMethodName' => [
                'title' => _t(__CLASS__ . '.COLUMN_METHOD_DEFAULT', 'Default method')
            ],
            'HasSkippedMFARegistration' => [
                'title' => _t(__CLASS__ . '.COLUMN_SKIPPED_REGISTRATION', 'Skipped registration'),
                'formatting' => function ($_, $record) {
                    return $record->HasSkippedMFARegistration ? 'Yes' : 'No';
                },
            ],
        ];

        $this->extend('updateColumns', $columns);

        return $columns;
    }

    /**
     * Provides the fields used to gather input to filter the report
     *
     * @return FieldList
     */
    public function parameterFields(): FieldList
    {
        $parameterFields = FieldList::create([
            TextField::create(
                'Member',
                singleton(Member::class)->i18n_singular_name()
            )->setDescription(_t(
                __CLASS__ . '.FILTER_MEMBER_DESCRIPTION',
                'Firstname, Surname, Email partial match search'
            )),
            DropdownField::create(
                'Methods',
                _t(__CLASS__ . '.COLUMN_METHODS_REGISTERED', 'Registered methods'),
                $this->getRegisteredMethodOptions()
            )->setHasEmptyDefault(true),
            DropdownField::create(
                'Skipped',
                _t(__CLASS__ . '.COLUMN_SKIPPED_REGISTRATION', 'Skipped registration'),
                [ 'no' => 'No', 'yes' => 'Yes' ]
            )->setHasEmptyDefault(true),
        ]);

        $this->extend('updateParameterFields', $parameterFields);

        return $parameterFields;
    }

    /**
     * Create an array mapping authentication method Class Names to Readable Names
     *
     * @return array
     */
    protected function getMethodClassToTitleMapping(): array
    {
        $mapping = [];

        foreach (MethodRegistry::singleton()->getMethods() as $method) {
            $mapping[get_class($method)] = $method->getName();
        }

        return $mapping;
    }

    /**
     * @return ArrayList<RegisteredMethod>
     */
    protected function getRegisteredMethodsForRecords(): ArrayList
    {
        if ($this->registeredMethods instanceof ArrayList) {
            return $this->registeredMethods;
        }

        // Get the members from the generated report field list
        /** @var DataList $members $members */
        $members = $this->getReportField()->getList();

        // Filter RegisteredMethods by the IDs of those members and convert it to an ArrayList (to prevent filters ahead
        // from executing the datalist more than once)
        $this->registeredMethods = ArrayList::create(
            RegisteredMethod::get()
                ->filter('MemberID', $members->column())
                ->exclude('MethodClassName', $this->getBackupMethodClass())
                ->toArray()
        );

        return $this->registeredMethods;
    }

    /**
     * Adds "None" and "Any" options to the registered method dropdown filter
     *
     * @return array
     */
    private function getRegisteredMethodOptions(): array
    {
        $methods = [
            'none' => _t(__CLASS__ . '.NONE', 'None'),
            'any' => _t(__CLASS__ . '.ANY_AT_LEAST_ONE', 'Any (at least one)'),
        ] + $this->getMethodClassToTitleMapping();
        unset($methods[$this->getBackupMethodClass()]);

        return $methods;
    }

    /**
     * Returns the PHP class name of the configured backup method in the MFA module
     *
     * @return string
     */
    private function getBackupMethodClass(): string
    {
        return get_class(singleton(MethodRegistry::class)->getBackupMethod());
    }
}
