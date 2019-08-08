<?php declare(strict_types=1);

namespace SilverStripe\MFA\Report;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\ORM\ArrayList;
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
     * @var DataList|null
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
     * Supplies the list displayed in the report. Required method for {@see Report} to work, but is not inherited API
     *
     * @param array $params
     * @return DataList
     */
    public function sourceRecords($params): DataList
    {
        $map = [
            'Member' => ['FirstName:PartialMatch', 'Surname:PartialMatch', 'Email:PartialMatch'],
            'Methods' => 'RegisteredMFAMethods.MethodClassName',
            'Skipped' => 'HasSkippedMFARegistration',
        ];
        $this->extend('updateParameterMap', $map);

        $sourceList = Member::get();

        // Cull empty strings from the list of params
        $params = array_filter($params, 'strlen');

        foreach ($map as $submissionName => $searchKey) {
            if (isset($params[$submissionName])) {
                if (is_array($searchKey)) {
                    $sourceList = $sourceList->filterAny(array_fill_keys($searchKey, $params[$submissionName]));
                } else {
                    $sourceList = $sourceList->filter($searchKey, $params[$submissionName]);
                }
            }
        }

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
            'methodCount' => [
                'title' => _t(__CLASS__ . '.COLUMN_METHOD_COUNT', 'Number of registered methods'),
                'formatting' => [$this, 'formatMethodCountColumn']
            ],
            'registeredMethods' => [
                'title' => _t(__CLASS__ . '.COLUMN_METHODS_REGISTERED', 'Registered methods'),
                'formatting' => [$this, 'formatMethodsColumn']
            ],
            'defaultMethodName' => [
                'title' => _t(__CLASS__ . '.COLUMN_METHOD_DEFAULT', 'Default method'),
                'formatting' => [$this, 'formatDefaultMethodColumn']
            ],
            'skippedRegistration' => [
                'title' => _t(__CLASS__ . '.COLUMN_SKIPPED_REGISTRATION', 'Skipped registration'),
                'formatting' => function ($_, $record) { return $record->HasSkippedMFARegistration ? 'Yes' : 'No'; }
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
        $methods = $this->getMethodClassToTitleMapping();
        unset($methods[$this->getBackupMethodClass()]);
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
                $methods
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
     * Produce a string that indicates the number of registered MFA methods for a given member
     *
     * @param null $_
     * @param Member $record
     * @return string
     */
    public function formatMethodCountColumn($_, Member $record): string
    {
        return (string) $this->getRegisteredMethodsForRecords()->filter('MemberID', $record->ID)->count();
    }

    /**
     * Produce a string that indicates the names of registered methods for a given member
     *
     * @param null $_
     * @param Member $record
     * @return string
     */
    public function formatMethodsColumn($_, Member $record): string
    {
        /** @var Member&MemberExtension $record */
        $methods = $this->getRegisteredMethodsForRecords();

        return implode(', ', array_map(function(RegisteredMethod $method) {
            return $method->getMethod()->getName();
        }, $methods->filter('MemberID', $record->ID)->toArray()));
    }

    /**
     * Produce a string that indicates the name of the default registered method for a member
     *
     * @param null $_
     * @param Member $record
     * @return string
     */
    public function formatDefaultMethodColumn($_, Member $record): string
    {
        /** @var Member&MemberExtension $record */
        /** @var RegisteredMethod|null $method */
        $method = $this->getRegisteredMethodsForRecords()->byID($record->DefaultRegisteredMethodID);

        if (!$method) {
            return '';
        }

        return $method->getMethod()->getName();
    }

    /**
     * Override the source params method to ensure boolean fields are filtered correctly. 0 can't be used as a value in
     * the FormField or the "No" option won't select correctly on page refresh
     *
     * @inheritDoc
     */
    protected function getSourceParams()
    {
        $params = parent::getSourceParams();

        if (isset($params['Skipped'])) {
            $params['Skipped'] = $params['Skipped'] === 'no' ? 0 : 1;
        }

        return $params;
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
     * @return ArrayList
     */
    protected function getRegisteredMethodsForRecords(): ArrayList
    {
        if ($this->registeredMethods instanceof ArrayList) {
            return $this->registeredMethods;
        }

        // Get the members from the generated report field list
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
     * Returns the PHP class name of the configured backup method in the MFA module
     *
     * @return string
     */
    private function getBackupMethodClass(): string
    {
        return get_class(singleton(MethodRegistry::class)->getBackupMethod());
    }
}
