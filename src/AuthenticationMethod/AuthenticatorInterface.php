<?php
namespace SilverStripe\MFA\AuthenticationMethod;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\MFA\SessionStore;

interface AuthenticatorInterface
{
    /**
     * Prepare this authentication method to verify a member by initialising state in session and generating details to
     * provide to a frontend React component
     *
     * @param SessionStore $store An object that hold session data (and the Member) that can be mutated
     * @return array Props to be passed to a front-end React component
     */
    public function start(SessionStore $store);

    /**
     * Verify the request has provided the right information to verify the member that aligns with any sessions state
     * that may have been set prior
     *
     * @param HTTPRequest $request
     * @param SessionStore $store
     * @return bool
     */
    public function verify(HTTPRequest $request, SessionStore $store);

    /**
     * Provide a string (possibly passed through i18n) that serves as a lead in for choosing this option for
     * authentication
     *
     * eg. "Enter one of your recovery codes"
     *
     * @return string
     */
    public function getLeadInLabel();
}
