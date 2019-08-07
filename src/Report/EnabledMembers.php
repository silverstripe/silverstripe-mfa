<?php declare(strict_types=1);

use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Service\MethodRegistry;

if (!class_exists(SS_Report::class)) {
    return;
}

class EnabledMembers extends SS_Report
{
    protected $title = 'Multi-factor authentication status of members';

    protected $description = 'Identifies all members on this site, with columns added to indicate the status each has'
        . ' in regards to multi-factor authentication method registration.';

    protected $dataClass = Member::class;

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
     * @return SS_List
     */
    public function sourceRecords($params): SS_List
    {
        $methods = $this->getMethodClassToTitleMapping();

        // Any GridFieldAction will submit all form inputs, regardless of whether or not they have valid values.
        // This results in zero results when no filters have been set, as the query then filters for empty string
        // on all parameters. `stlen` is a safe function, as all user input comes through to PHP as strings.
        $params = array_filter($params, 'strlen');

        // Having a WHERE limits the results, which means the the COUNT will be off (GROUP BY applied after WHERE)
        // So clip the count filter and apply it after the database query, only if Methods filter is also set
        // this way we can delegate to the database where possible, which should be faster.
        if (isset($params['Count'])) {
            $desiredCount = (int)$params['Count'];
            if (isset($params['Methods'])) {
                $countFilter = $desiredCount;
                unset($params['Count']);
            } elseif ($desiredCount) {
                // When querying SQL COUNT() - this will always factor in the backup method, which is excluded in the
                // secondary query below to get the list of method names the user has registered. So to compensate we
                // need to bump up the entered number by 1.
                $params['Count'] = $desiredCount + 1;
            }
        }

        $filteredList = ArrayList::create([]);
        $backupClass = $this->getBackupMethodClass();
        /** @var Member[]&MemberExtension[] $members */
        $members = $this->applyParams(Member::get(), $params)->toArray();
        foreach ($members as $member) {
            $defaultMethod = $member->getDefaultRegisteredMethod();
            $defaultMethodClassName = $defaultMethod ? $defaultMethod->MethodClassName : '';

            $registeredMethods = $member
                ->RegisteredMFAMethods()
                ->exclude('MethodClassName', $backupClass)
                ->column('MethodClassName');
            $registeredMethodNames = array_map(function (string $methodClass) use ($methods): string {
                return $methods[$methodClass];
            }, $registeredMethods);

            $memberReportData = ArrayData::create([]);
            foreach ($member->summaryFields() as $field => $name) {
                $memberReportData->$field = $member->$field;
            }

            $memberReportData->methodCount = (string)count($registeredMethods);
            $memberReportData->registeredMethods = implode(', ', $registeredMethodNames);
            $memberReportData->defaultMethodName = $methods[$defaultMethodClassName] ?? '';
            $memberReportData->skippedRegistration = $member->dbObject('HasSkippedMFARegistration')->Nice();

            $filteredList->push($memberReportData);
        };
        if (isset($countFilter)) {
            $filteredList = $filteredList->filter('methodCount', $countFilter);
        }
        $this->extend('updateSourceRecords', $filteredList);
        return $filteredList;
    }

    /**
     * List the columns configured to display in the resulting reports GridField
     *
     * @return array
     */
    public function columns(): array
    {
        $columns = singleton(Member::class)->summaryFields() + [
            'methodCount' => _t(__CLASS__ . '.COLUMN_METHOD_COUNT', '№ methods'),
            'registeredMethods' => _t(__CLASS__ . '.COLUMN_METHODS_REGISTERED', 'Method names'),
            'defaultMethodName' => _t(__CLASS__ . '.COLUMN_METHOD_DEFAULT', 'Default method'),
            'skippedRegistration' => _t(__CLASS__ . '.COLUMN_SKIPPED_REGISTRATION', 'Skipped registration'),
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
        $parameterFields = FieldList::create(
            TextField::create(
                'Member',
                singleton(Member::class)->i18n_singular_name()
            )->setDescription(_t(
                __CLASS__ . '.FILTER_MEMBER_DESCRIPTION',
                'Firstname, Surname, Email partial match search'
            )),
            NumericField::create(
                'Count',
                _t(__CLASS__ . '.COLUMN_METHOD_COUNT', 'Number of registered methods')
            ),
            DropdownField::create(
                'Methods',
                _t(__CLASS__ . '.COLUMN_METHODS_REGISTERED', 'Registered methods'),
                $methods,
                ''
            )->setHasEmptyDefault(true),
            CheckboxField::create(
                'Skipped',
                _t(__CLASS__ . '.COLUMN_SKIPPED_REGISTRATION', 'Skipped registration')
            )
        );
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
        $methods = singleton(MethodRegistry::class)->getMethods();
        $methodsClasses = array_map(
            function (MethodInterface $method): string {
                return get_class($method);
            },
            $methods
        );
        $methodNames = array_map(
            function (MethodInterface $method): string {
                return $method->getName();
            },
            $methods
        );
        return array_combine($methodsClasses, $methodNames);
    }

    /**
     * Applies parameters to source records for filtering purposes.
     *
     * @param SS_Filterable $params
     * @param array $params
     * @return SS_List
     */
    protected function applyParams(SS_Filterable $sourceList, array $params): SS_List
    {
        $map = [
            'Member' => ['FirstName:PartialMatch', 'Surname:PartialMatch', 'Email:PartialMatch'],
            'Methods' => 'RegisteredMFAMethods.MethodClassName',
            'Skipped' => 'HasSkippedMFARegistration',
        ];
        $this->extend('updateParameterMap', $map);
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
     * Returns the PHP class name of the configured backup method in the MFA module
     *
     * @return string
     */
    private function getBackupMethodClass(): string
    {
        return get_class(singleton(MethodRegistry::class)->getBackupMethod());
    }
}
