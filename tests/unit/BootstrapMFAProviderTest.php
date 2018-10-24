<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Models\BackupCode;
use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use Firesphere\BootstrapMFA\Tests\Helpers\CodeHelper;
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
        /** @var BootstrapMFAProvider $provider */
        $provider = Injector::inst()->get(BootstrapMFAProvider::class);
        $provider->updateTokens();

        $this->assertCount(0, CodeHelper::getCodesFromSession());
    }

    public function testResultFound()
    {
        $result = null;
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');
        /** @var BootstrapMFAProvider $provider */
        $provider = Injector::inst()->get(BootstrapMFAProvider::class);
        $provider->setMember($member);
        $token = $member->encryptWithUserSettings('123345');
        $result = $provider->fetchToken($token);

        // No backupcodes generated yet
        $this->assertNull($result);

        Security::setCurrentUser($member);
        $provider->updateTokens();

        $tokens = CodeHelper::getCodesFromSession();

        $token = $member->encryptWithUserSettings($tokens[0]);
        $result = $provider->fetchToken($token);

        // New backupcodes
        $this->assertInstanceOf(BackupCode::class, $result);
    }
}
