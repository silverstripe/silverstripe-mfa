<?php declare(strict_types=1);

CMSMenu::remove_menu_item('SilverStripe-MFA-Controller-AdminRegistrationController');

// Remove the default authenticator in place of our own (see config.yml)
Authenticator::unregister_authenticator('MemberAuthenticator');
