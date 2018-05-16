<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use Firesphere\BootstrapMFA\Tests\Helpers\CodeHelper;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class BootstrapMFAProviderTest extends SapphireTest
{
    protected static $fixture_file = '../fixtures/member.yml';

    public function testUpdateTokens()
    {
        $member = $this->objFromFixture(Member::class, 'member1');
        Security::setCurrentUser($member);
        /** @var BootstrapMFAProvider $provider */
        $provider = Injector::inst()->get(BootstrapMFAProvider::class);
        $provider->setMember($member);
        $provider->updateTokens();

        $this->assertEquals(15, count(CodeHelper::getCodesFromSession()));
    }

    public function testUpdateTokensWithoutMember()
    {
        /** @var BootstrapMFAProvider $provider */
        $provider = Injector::inst()->get(BootstrapMFAProvider::class);
        $provider->updateTokens();

        $this->assertEquals(0, count(CodeHelper::getCodesFromSession()));
    }

    public function testResultCreated()
    {
        $result = null;
        $member = $this->objFromFixture(Member::class, 'member1');
        $provider = Injector::inst()->get(BootstrapMFAProvider::class);

        $provider->verifyToken('123345', $result);
        $provider->setMember($member);

        $this->assertInstanceOf(ValidationResult::class, $result);
    }
}
