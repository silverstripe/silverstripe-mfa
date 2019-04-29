<?php

namespace SilverStripe\MFA\Service;

use SilverStripe\Control\Director;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ViewableData;

/**
 * Represents an event that a user will be notified about, and also encapsulates any data associated with that event.
 */
abstract class NotificationEvent extends ViewableData
{
    /**
     * Handlers configured to send notifications about this event
     * Should be an array of {@see NotificationInterface} implementation class names
     * But could also be a simple string (still a class name of the same interface).
     *
     * @config
     * @var string[]
     */
    private static $handlers = [];

    /**
     * Default title for this event type.
     * Passed as the default value for {@see i18n::_t} via {@see self::getTitle}
     * The assumption being that the default locale will match that of the given string.
     *
     * @config
     * @var string
     */
    private static $title = '';

    /**
     * Default description for this event type.
     * Passed as the default value for {@see i18n::_t} via {@see self::getDescription}
     * The assumption being that the default locale will match that of the given string.
     *
     * @config
     * @var string
     */
    private static $description = '';

    /**
     * Which member triggered this event - can be null to represent a system user (e.g. site itself/queued job, etc.)
     *
     * @var Member|null
     */
    protected $member = null;

    /**
     * Arbitrary data that is relevant to this event
     * e.g. the event of receiving an email may say show a notification with the "from" or "subject" fields (or both)
     * Encapsulated in order to provide a defined interface for fetching the data across different event types.
     *
     * @var array
     */
    protected $data = [];

    public function __construct(?Member $member = null, array $data = [])
    {
        $data['SiteAddress'] = Director::absoluteBaseURL();
        if (class_exists(SiteConfig::class)) {
            $data['SiteName'] = SiteConfig::current_site_config()->Title ?: 'A SilverStripe Site';
        }
        $this->setData($data);
        $this->setMember($member);
    }

    public function __get($property)
    {
        return $this->getDatum($property) ?: parent::__get($property);
    }

    public function getTitle()
    {
        return _t(static::class . '.TITLE', $this->config()->get('title'));
    }

    public function getDescription()
    {
        return _t(static::class . '.DESCRIPTION', $this->config()->get('description'));
    }

    public function setMember(Member $member): self
    {
        $this->member = $member;
        return $this;
    }

    public function getMember()
    {
        return $this->member;
    }

    /**
     * Add data to the data set
     * This will overwrite existing values by performing a merge, but will not clear values not included in the given
     * set. In order to clear other values that aren't passed through, first use clearData {@see self::clearData()}
     *
     * @param array $data
     * @return self
     */
    public function setData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Add a single value to the data set
     *
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function setDatum(string $name, $value): self
    {
        return $this->setData([$name => $value]);
    }

    /**
     * Fetch a single value in the data set
     *
     * @param string $key
     * @return mixed
     */
    public function getDatum(string $name)
    {
        return array_key_exists($name, $this->data) ? $this->data[$name] : null;
    }

    /**
     * Wipe all data from this event
     * Does not preserve default data added via the constructor
     *
     * @return self
     */
    public function clearData(): self
    {
        $this->data = [];
        return $this;
    }

    /**
     * Find templates for this event, with possible hanlder type (used as an 'action' argument in template lookup)
     * e.g. Email notification handler, the template might be MethodAdded_Email.ss
     * Will automatically trim down fully qualified class names to the unqualified class name if they're passed in as
     * the handler type.
     * If a handler type is provided it will take prescedence in the lookup chain over the non-suffix template.
     *
     * @param string $handlerType
     * @return string[]
     */
    public function getTemplates($handlerType = ''): array
    {
        $templates = SSViewer::get_templates_by_class(static::class, '', self::class);
        if ($handlerType) {
            // clean up fully qualified class names
            $segments = explode('\\', $handlerType);
            $action = array_pop($segments);

            $templates = array_merge(
                SSViewer::get_templates_by_class(static::class, $action, self::class),
                $templates
            );
        }
        return $templates;
    }
}
