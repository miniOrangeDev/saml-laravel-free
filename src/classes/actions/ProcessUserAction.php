<?php

namespace MiniOrange\Classes\Actions;

use MiniOrange\Helper\PluginSettings;

class ProcessUserAction
{
    private $attrs;
    private $relayState;
    private $sessionIndex;


    /**
     * LogUserInAction constructor.
     * @param $attrs        - all the user profile attributes send by the IDP in the SAML response
     * @param $relayState   - the URL that the user needs to be redirected to
     * @param $sessionIndex - the session Index parameter provided by the IDP ( used for Single Logout Purposes )
     */
    public function __construct($attrs, $relayState, $sessionIndex)
    {
        $this->attrs = $attrs;
        $this->relayState = $relayState;
        $this->sessionIndex = $sessionIndex;

    }


    function execute()
    {
        $pluginSettings = PluginSettings::getPluginSettings();
        $pluginSettings->setSessionIndex($this->sessionIndex);
        // TODO : Write your code here to login/create users
    }


}