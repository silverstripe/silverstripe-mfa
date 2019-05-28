<?php

namespace SilverStripe\MFA\Extension;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Core\Extension;
use SilverStripe\MFA\RequestHandler\BaseHandlerTrait;

class RequirementsExtension extends Extension
{
    use BaseHandlerTrait;

    /**
     * @see LeftAndMain::init()
     */
    public function init()
    {
        // As requirements for this module are dynamic - plugin methods apply their own requirements - we need to
        // include these requirements at run-time (opposed to using $extra_requirements_*)
        $this->applyRequirements(false);
    }
}
