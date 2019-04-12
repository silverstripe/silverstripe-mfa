<?php declare(strict_types=1);

namespace SilverStripe\MFA\Store;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

/**
 * This class provides an interface to store data in session during an MFA process. This is implemented as a measure to
 * prevent bleeding state between individual MFA auth types
 *
 * @package SilverStripe\MFA
 */
class SessionStore implements StoreInterface
{
    const SESSION_KEY = 'MFASessionStore';

    /**
     * The member that is currently going through the MFA process
     *
     * @var Member
     */
    protected $member;

    /**
     * A string representing the current authentication method that is underway
     *
     * @var string
     */
    protected $method;

    /**
     * Any state that the current authentication method needs to retain while it is underway
     *
     * @var array
     */
    protected $state = [];

    /**
     * Attempt to create a store from the given request getting any existing state from the session of the request
     *
     * {@inheritdoc}
     */
    public function __construct(HTTPRequest $request = null)
    {
        $state = $request ? $request->getSession()->get(static::SESSION_KEY) : null;

        if ($state && $state['member']) {
            /** @var Member $member */
            $member = DataObject::get_by_id(Member::class, $state['member']);

            $this->setMember($member);
            $this->setMethod($state['method']);
            $this->setState($state['state']);
        }
    }

    /**
     * @return Member&MemberExtension|null
     */
    public function getMember(): ?Member
    {
        return $this->member;
    }

    /**
     * @param Member $member
     * @return $this
     */
    public function setMember(Member $member): StoreInterface
    {
        // Early return if there's no change
        if ($this->member && $this->member->ID === $member->ID) {
            return $this;
        }

        // If the member has changed we should null out the method that's underway and the state of it
        $this->resetMethod();

        $this->member = $member;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method): StoreInterface
    {
        $this->method = $method;

        return $this;
    }

    public function getState(): array
    {
        return $this->state;
    }

    public function setState(array $state): StoreInterface
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Save this store into the session of the given request
     *
     * {@inheritdoc}
     */
    public function save(HTTPRequest $request): StoreInterface
    {
        $request->getSession()->set(static::SESSION_KEY, $this->build());

        return $this;
    }

    /**
     * Clear any stored values for the given request
     *
     * {@inheritdoc}
     */
    public static function clear(HTTPRequest $request): void
    {
        $request->getSession()->clear(static::SESSION_KEY);
    }

    protected function resetMethod(): StoreInterface
    {
        $this->setMethod(null)->setState([]);

        return $this;
    }

    protected function build(): array
    {
        return [
            'member' => $this->getMember() ? $this->getMember()->ID : null,
            'method' => $this->getMethod(),
            'state' => $this->getState(),
        ];
    }
}
