<?php declare(strict_types=1);

namespace SilverStripe\MFA\Store;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\Security\Member;

/**
 * Represents a place for temporarily storing state related to a login or registration attempt.
 */
interface StoreInterface
{
    /**
     * Persist the stored state for the given request
     *
     * @param HTTPRequest $request
     * @return StoreInterface
     */
    public function save(HTTPRequest $request): StoreInterface;

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
     * Update the state in the store
     *
     * @param array $state
     * @return StoreInterface
     */
    public function setState(array $state): StoreInterface;

    /**
     * @return Member&MemberExtension|null
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
     * @param string $method
     * @return $this
     */
    public function setMethod($method): StoreInterface;
}
