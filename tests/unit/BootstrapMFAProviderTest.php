<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Models\BackupCode;
use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use Firesphere\BootstrapMFA\Tests\Helpers\CodeHelper;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
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

        $this->assertCount(15, CodeHelper::getCodesFromSession());
    }

    public function testUpdateTokensWithoutMember()
    {
        Controller::curr()->getRequest()->getSession()->clear('tokens');
        /** @var BootstrapMFAProvider $provider */
        $provider = Injector::inst()->get(BootstrapMFAProvider::class);
        $provider->setMember(null);
        $provider->updateTokens();

        $this->assertCount(0, CodeHelper::getCodesFromSession(), 'No member, no codes');
    }

    public function testFetchToken()
    {
        Controller::curr()->getRequest()->getSession()->clear('tokens');
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');
        /** @var BootstrapMFAProvider $provider */
        $provider = Injector::inst()->get(BootstrapMFAProvider::class);
        $provider->setMember($member);
        $result = $provider->fetchToken();

        $this->assertCount(0, $result, 'There should be no tokens set up for the member yet');

        Security::setCurrentUser($member);
        $provider->updateTokens();

        $result = $provider->fetchToken();

        $this->assertCount(15, $result, 'A new set of backup codes');
        // New backupcodes
        $this->assertInstanceOf(BackupCode::class, $result->first());
        $provider->updateTokens();

        $result = $provider->fetchToken();

        $this->assertCount(15, $result, 'New backup codes are created and old ones thrown out');
        // New backupcodes
        $this->assertInstanceOf(BackupCode::class, $result->first());
    }
}
