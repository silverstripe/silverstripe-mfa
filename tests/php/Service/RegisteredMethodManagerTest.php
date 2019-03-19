<?php

namespace SilverStripe\MFA\Tests\Service;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\Security\Member;

class RegisteredMethodManagerTest extends SapphireTest
{
    protected static $fixture_file = 'RegisteredMethodManagerTest.yml';

    protected static $required_extensions = [
        Member::class => [
            MemberExtension::class,
        ],
    ];

    public function testGetFromMember()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');

        $result = RegisteredMethodManager::singleton()->getFromMember($member, 'backup-codes');
        $this->assertInstanceOf(MethodInterface::class, $result);
    }

    public function testGetFromMemberReturnsNullWhenNotFound()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');

        $result = RegisteredMethodManager::singleton()->getFromMember($member, 'open-sesame');
        $this->assertInstanceOf(MethodInterface::class, $result);
    }
}
