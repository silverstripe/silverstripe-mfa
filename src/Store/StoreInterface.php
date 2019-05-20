<?php declare(strict_types=1);

namespace SilverStripe\MFA\Store;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\MFA\Extension\MemberMFAExtension;
use SilverStripe\Security\Member;

/**
 * Represents a place for temporarily storing state related to a login or registration attempt.
 */
interface StoreInterface
{
    /**
     * Create a new StoreInterface, optionally given an HTTPRequest object
     *
     * @param Member $member
     */
    public function __construct(Member $member);

    /**
     * Persist the stored state for the given request
     *
     * @param HTTPRequest $request
     * @return StoreInterface
     */
    public function save(HTTPRequest $request): StoreInterface;

    /**
     * Load a StoreInterface from the given request and return it if it exists
     *
     * @param HTTPRequest $request
     * @return StoreInterface|null
     */
    public static function load(HTTPRequest $request): ?StoreInterface;

    /**
     * Clear any stored state for the given request
     *
     * @param HTTPRequest $request
     * @return void
     */
    public static function clear(HTTPRequest $request): void;

    /**
     * Get the state from the store
     *
     * @return array
     */
    public function getState(): array;

    /**
     * Update the state in the store. Will override existing state. To add to the existing state use addState().
     *
     * @param array $state
     * @return StoreInterface
     */
    public function setState(array $state): StoreInterface;

    /**
     * Add to the state in the store
     *
     * @param array $state
     * @return StoreInterface
     */
    public function addState(array $state): StoreInterface;

    /**
     * @return Member&MemberMFAExtension|null
     */
    public function getMember(): ?Member;

    /**
     * @param Member $member
     * @return $this
     */
    public function setMember(Member $member): StoreInterface;

    /**
     * @return string
     */
    public function getMethod(): ?string;

    /**
     * @param string|null $method
     * @return $this
     */
    public function setMethod(?string $method): StoreInterface;

    /**
     * Add and keep track of methods that have been verified
     *
     * @param string $method
     * @return StoreInterface
     */
    public function addVerifiedMethod(string $method): StoreInterface;

    /**
     * Get the list of methods that have been verified
     *
     * @return string[]
     */
    public function getVerifiedMethods(): array;
}
