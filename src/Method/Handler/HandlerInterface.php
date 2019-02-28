<?php

namespace SilverStripe\MFA\Method\Handler;

use SilverStripe\MFA\Store\StoreInterface;

/**
 * A base interface for both login and register interfaces, which both "start" their process
 */
interface HandlerInterface
{
    /**
     * Stores any data required to handle a multi-request process, and returns relevant state to be applied to the
     * front-end application managing the process.
     *
     * @param StoreInterface $store An object that hold session data (and the Member) that can be mutated
     * @return array Props to be passed to a front-end component
     */
    public function start(StoreInterface $store);
}
