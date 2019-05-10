<?php
namespace MiniOrange\Classes\Actions;

use MiniOrange\Classes\AuthnRequest;
use MiniOrange\Helper\Constants;
use MiniOrange\Helper\Exception\NoIdentityProviderConfiguredException;
use MiniOrange\Helper\PluginSettings;
use MiniOrange\Helper\Utilities;

class SendAuthnRequest
{

    /**
     * Execute function to execute the classes function.
     *
     * @throws \Exception
     * @throws NoIdentityProviderConfiguredException
     */
    public static function execute()
    {
        $pluginSettings = PluginSettings::getPluginSettings();

        if (! Utilities::isSPConfigured())
            throw new NoIdentityProviderConfiguredException();

        $relayState = isset($_REQUEST['RelayState']) ? $_REQUEST['RelayState'] : '/';

        // generate the saml request

        $samlRequest = (new AuthnRequest($pluginSettings->getAcsUrl(), $pluginSettings->getSpEntityId(), $pluginSettings->getSamlLoginUrl(), $pluginSettings->getLoginBindingType(), true, true))->build();
        $bindingType = $pluginSettings->getLoginBindingType();
        // send saml request over
        if (empty($bindingType) || $bindingType == Constants::HTTP_REDIRECT)
            return (new HttpAction())->sendHTTPRedirectRequest($samlRequest, $relayState, $pluginSettings->getSamlLoginUrl());
        else
            (new HttpAction())->sendHTTPPostRequest($samlRequest, $relayState, $pluginSettings->getSamlLoginUrl());
    }
}