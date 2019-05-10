<?php

namespace MiniOrange\Classes\Actions;

use MiniOrange\Classes\SamlResponse;
use MiniOrange\Helper\Utilities;

/**
 * Handles reading of SAML Responses from the IDP. Read the SAML Response
 * from the IDP and process it to detect if it's a valid response from the IDP.
 * Generate a SAML Response Object and log the user in. Update existing user
 * attributes and groups if necessary.
 */
class ReadResponseAction
{
    /**
     * Execute function to execute the classes function.
     * @throws \Exception
     */
    public static function execute()
    {

        // read the response
        $samlResponse = $_REQUEST['SAMLResponse'];
        $relayState  = array_key_exists('RelayState', $_REQUEST) ? $_REQUEST['RelayState'] : '/';
        //decode the saml response
        $samlResponse = base64_decode($samlResponse);
        if(!array_key_exists('SAMLResponse', $_POST)) {
            $samlResponse = gzinflate($samlResponse);
        }

        $document = new \DOMDocument();
        $document->loadXML($samlResponse);
        $samlResponseXML = $document->firstChild;
        if($samlResponseXML->localName == 'LogoutResponse') return; //if logout response then return
        $samlResponse = new SamlResponse($samlResponseXML);	//convert the xml to SAML2Response object

        return $samlResponse;
    }
}