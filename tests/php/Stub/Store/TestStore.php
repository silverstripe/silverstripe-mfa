<?php

namespace SilverStripe\MFA\Tests\Stub\Store;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\Security\Member;

class TestStore implements StoreInterface, TestOnly
{
    public function __construct(Member $member)
    {
    }

    public function getMethod(): ?string
    {
        return null;
    }

    public function setMember(Member $member): StoreInterface
    {
        return $this;
    }

    public function setMethod(?string $method): StoreInterface
    {
        return $this;
    }

    public function getState(): array
    {
        return [];
    }

    public function setState(array $state): StoreInterface
    {
        return $this;
    }

    public function addState(array $state): StoreInterface
    {
        return $this;
    }

    public function addVerifiedMethod(string $method): StoreInterface
    {
        return $this;
    }

    public function getVerifiedMethods(): array
    {
        return [];
    }

    public function getMember(): ?Member
    {
        return null;
    }

    public static function clear(HTTPRequest $request): void
    {
    }

    public static function load(HTTPRequest $request): ?StoreInterface
    {
        return null;
    }

    public function save(HTTPRequest $request): StoreInterface
    {
        return $this;
    }
}
