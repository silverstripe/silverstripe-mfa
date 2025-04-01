<?php

namespace SilverStripe\MFA\Extension;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\Deprecation;
use SilverStripe\MFA\RequestHandler\BaseHandlerTrait;

/**
 * @extends Extension<LeftAndMain>
 */
class RequirementsExtension extends Extension
{
    use BaseHandlerTrait;

    /**
     * @see LeftAndMain::init()
     * @deprecated 5.4.0 Will be renamed to onInit()
     */
    public function init()
    {
        Deprecation::noticeWithNoReplacment('5.4.0', 'Will be renamed to onInit()');
        // As requirements for this module are dynamic - plugin methods apply their own requirements - we need to
        // include these requirements at run-time (opposed to using $extra_requirements_*)
        $this->applyRequirements(false);
    }
}
