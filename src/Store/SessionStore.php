<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Store;

use RuntimeException;
use Serializable;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\MFA\Exception\InvalidMethodException;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Dev\Deprecation;

/**
 * This class provides an interface to store data in session during an MFA process. This is implemented as a measure to
 * prevent bleeding state between individual MFA auth types
 *
 * @package SilverStripe\MFA
 */
class SessionStore implements StoreInterface, Serializable
{
    public const SESSION_KEY = 'MFASessionStore';

    /**
     * The member that is currently going through the MFA process
     *
     * @var Member
     */
    protected $member;

    /**
     * MemberID is only used on unserialising from session as we can defer the DB call for the member
     *
     * @var int
     */
    protected $memberID;

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
     * The URL segment identifiers of methods that have been verified in this session
     *
     * @var string[]
     */
    protected $verifiedMethods = [];

    /**
     * Attempt to create a store from the given request getting any existing state from the session of the request
     *
     * {@inheritdoc}
     */
    public function __construct(Member $member)
    {
        $this->setMember($member);
    }

    /**
     * @return Member&MemberExtension|null
     */
    public function getMember(): ?Member
    {
        if (!$this->member && $this->memberID) {
            $this->member = DataObject::get_by_id(Member::class, $this->memberID);
        }

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
        $this->memberID = $member->ID;

        // When the member changes the list of verified methods should reset
        $this->verifiedMethods = [];

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
     * @param string|null $method
     * @return $this
     */
    public function setMethod(?string $method): StoreInterface
    {
        if (in_array($method, $this->getVerifiedMethods() ?? [])) {
            throw new InvalidMethodException('You cannot verify with a method you have already verified');
        }

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

    public function addState(array $state): StoreInterface
    {
        $this->state = array_merge($this->state, $state);
        return $this;
    }

    public function addVerifiedMethod(string $method): StoreInterface
    {
        if (!in_array($method, $this->verifiedMethods ?? [])) {
            $this->verifiedMethods[] = $method;
        }

        return $this;
    }

    public function getVerifiedMethods(): array
    {
        return $this->verifiedMethods;
    }

    /**
     * Save this store into the session of the given request
     *
     * {@inheritdoc}
     */
    public function save(HTTPRequest $request): StoreInterface
    {
        $request->getSession()->set(static::SESSION_KEY, $this);

        return $this;
    }

    /**
     * Load a StoreInterface from the given request and return it if it exists
     *
     * @param HTTPRequest $request
     * @return StoreInterface|null
     */
    public static function load(HTTPRequest $request): ?StoreInterface
    {
        $store = $request->getSession()->get(static::SESSION_KEY);
        return $store instanceof self ? $store : null;
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

    /**
     * "Reset" the method currently in progress by clearing the identifier and state
     *
     * @return StoreInterface
     */
    protected function resetMethod(): StoreInterface
    {
        $this->setMethod(null)->setState([]);

        return $this;
    }

    public function __serialize(): array
    {
        // Use the stored member ID by default.
        // We should do this because we can avoid ever fetching the member object
        // from the database if the member was never accessed during this request.
        $memberID = $this->memberID;

        if (!$memberID && $this->getMember()) {
            $memberID = $this->getMember()->ID;
        }

        return [
            'member' => $memberID,
            'method' => $this->getMethod(),
            'state' => $this->getState(),
            'verifiedMethods' => $this->getVerifiedMethods(),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->memberID = $data['member'];
        $this->setMethod($data['method']);
        $this->setState($data['state']);
        foreach ($data['verifiedMethods'] as $method) {
            $this->addVerifiedMethod($method);
        }
    }

    /**
     * The __serialize() magic method will be automatically used instead of this
     *
     * @return string
     * @deprecated 4.7.0 Use __serialize() instead
     */
    public function serialize(): string
    {
        Deprecation::notice('4.7.0', 'Use __serialize() instead');
        $data = $this->__serialize();
        $str = json_encode($data);
        if (!$str) {
            throw new RuntimeException(json_last_error_msg());
        }
        return $str;
    }

    /**
     * The __unserialize() magic method will be automatically used instead of this almost all the time
     * This method will be automatically used if existing serialized data was not saved as an associative array
     * and the PHP version used in less than PHP 9.0
     *
     * @param string $serialized
     * @deprecated 4.7.0 Use __unserialize() instead
     */
    public function unserialize($serialized): void
    {
        Deprecation::notice('4.7.0', 'Use __unserialize() instead');
        $data = json_decode($serialized ?? '', true);
        $this->__unserialize($data);
    }
}
