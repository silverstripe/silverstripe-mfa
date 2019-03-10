<?php
namespace SilverStripe\MFA\Method\Handler;

/**
 * Handlers implementing this interface should come with React UI component associated with these handlers.
 */
interface UIComponentProviderInterface
{
    /**
     * Get the key that a React UI component is registered under (with @silverstripe/react-injector on the front-end)
     *
     * @return string
     */
    public function getComponent();
}
