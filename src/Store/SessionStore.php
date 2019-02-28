<?php
namespace SilverStripe\MFA\Store;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\MFA\Extension\MemberExtension;
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
     * Create a store from the given request getting any initial state from the session of the request
     *
     * {@inheritdoc}
     */
    public static function create(HTTPRequest $request)
    {
        $state = $request->getSession()->get(static::SESSION_KEY);

        $new = new static;

        if ($state) {
            $new->setMethod($state['method']);
            $new->setState($state['state']);
            if ($state['member']) {
                $new->setMember(Member::get_by_id($state['member']));
            }
        }

        return $new;
    }

    /**
     * @return Member|MemberExtension
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param Member $member
     * @return $this
     */
    public function setMember(Member $member)
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
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState(array $state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Save this store into the session of the given request
     *
     * {@inheritdoc}
     */
    public function save(HTTPRequest $request)
    {
        $request->getSession()->set(static::SESSION_KEY, $this->build());

        return $this;
    }

    /**
     * Clear any stored values for the given request
     *
     * {@inheritdoc}
     */
    public static function clear(HTTPRequest $request)
    {
        $request->getSession()->clear(static::SESSION_KEY);
    }

    protected function resetMethod()
    {
        $this->setMethod(null)->setState([]);

        return $this;
    }

    protected function build()
    {
        return [
            'member' => $this->getMember() ? $this->getMember()->ID : null,
            'method' => $this->getMethod(),
            'state' => $this->getState(),
        ];
    }
}
