<?php

namespace SilverStripe\MFA\RequestHandler;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\Security\Member;
use SilverStripe\Security\SudoMode\SudoModeServiceInterface;
use SilverStripe\View\Requirements;

trait BaseHandlerTrait
{
    /**
     * A "session store" object that helps contain MFA specific session detail
     *
     * @var StoreInterface
     */
    protected $store;

    /**
     * Perform the necessary "Requirements" calls to ensure client side scripts are available in the response
     *
     * @param bool $frontEndRequirements Indicates dependencies usually provided by admin should also be required
     */
    protected function applyRequirements(bool $frontEndRequirements = true): void
    {
        // Run through requirements
        if ($frontEndRequirements) {
            Requirements::javascript('silverstripe/mfa: client/dist/js/injector.js');
            Requirements::javascript('silverstripe/admin: client/dist/js/i18n.js');
        }

        // Plugin module requirements
        foreach (MethodRegistry::singleton()->getMethods() as $method) {
            $method->applyRequirements();
        }

        Requirements::add_i18n_javascript('silverstripe/mfa: client/lang');

        $suffix = $frontEndRequirements ? '' : '-cms';
        Requirements::javascript("silverstripe/mfa: client/dist/js/bundle{$suffix}.js");
        Requirements::css("silverstripe/mfa: client/dist/styles/bundle{$suffix}.css");
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
     * @param StoreInterface $store
     * @return $this
     */
    public function setStore(StoreInterface $store): self
    {
        $this->store = $store;
        return $this;
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

    /**
     * Returns a sudo mode service instance
     *
     * @return SudoModeServiceInterface
     */
    protected function getSudoModeService(): SudoModeServiceInterface
    {
        return Injector::inst()->get(SudoModeServiceInterface::class);
    }
}
