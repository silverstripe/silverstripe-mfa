<?php

namespace SilverStripe\MFA\Extension;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Core\Extension;
use SilverStripe\MFA\RequestHandler\BaseHandlerTrait;

/**
 * @extends Extension<LeftAndMain>
 */
class RequirementsExtension extends Extension
{
    use BaseHandlerTrait;

    /**
     * @see LeftAndMain::init()
     */
    protected function onInit()
    {
        // As requirements for this module are dynamic - plugin methods apply their own requirements - we need to
        // include these requirements at run-time (opposed to using $extra_requirements_*)
        $this->applyRequirements(false);
    }
}
