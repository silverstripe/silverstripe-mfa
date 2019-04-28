<?php

namespace SilverStripe\MFA\RequestHandler;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\Security\Member;
use SilverStripe\View\Requirements;

trait BaseHandlerTrait
{
    /**
     * Perform the necessary "Requirements" calls to ensure client side scripts are available in the response
     */
    protected function applyRequirements(): void
    {
        // Run through requirements
        Requirements::javascript('silverstripe/mfa: client/dist/js/injector.js');
        Requirements::javascript('silverstripe/admin: client/dist/js/i18n.js');
        Requirements::javascript('silverstripe/mfa: client/dist/js/bundle.js');
        Requirements::add_i18n_javascript('silverstripe/mfa: client/lang');
        Requirements::css('silverstripe/mfa: client/dist/styles/bundle.css');

        // Plugin module requirements
        foreach (MethodRegistry::singleton()->getMethods() as $method) {
            $method->applyRequirements();
        }
    }

    /**
     * @return StoreInterface|null
     */
    protected function getStore(): ?StoreInterface
    {
        if (!$this->store) {
            $spec = Injector::inst()->getServiceSpec(StoreInterface::class);
            $class = is_string($spec) ? $spec : $spec['class'];
            $this->store = call_user_func([$class, 'load'], $this->getRequest());
        }

        return $this->store;
    }

    /**
     * @param Member $member
     * @return StoreInterface
     */
    protected function createStore(Member $member): StoreInterface
    {
        $store = Injector::inst()->create(StoreInterface::class, $member);
        // Ensure use of the getter returns the new store
        $this->store = $store;
        return $store;
    }
}
