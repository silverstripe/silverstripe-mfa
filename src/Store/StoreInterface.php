<?php

namespace SilverStripe\MFA\Store;

use SilverStripe\Control\HTTPRequest;

/**
 * Represents a place for temporarily storing state related to a login or registration attempt.
 */
interface StoreInterface
{
    /**
     * Create a store from the given request, using any initial state related to the request that has been persisted
     *
     * @param HTTPRequest $request
     * @return StoreInterface
     */
    public static function create(HTTPRequest $request);

    /**
     * Get the state from the store
     *
     * @return array
     */
    public function getState();

    /**
     * Update the state in the store
     *
     * @param array $state
     * @return StoreInterface
     */
    public function setState(array $state);

    /**
     * Persist the stored state for the given request
     *
     * @param HTTPRequest $request
     * @return StoreInterface
     */
    public function save(HTTPRequest $request);

    /**
     * Clear any stored state for the given request
     *
     * @param HTTPRequest $request
     * @return void
     */
    public static function clear(HTTPRequest $request);
}
