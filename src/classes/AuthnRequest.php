<?php

namespace MiniOrange\Classes;

use MiniOrange\Helper\Constants;
use MiniOrange\Helper\PluginSettings;
use MiniOrange\Helper\SAMLUtilities;

/**
 * This class is used to generate our AuthnRequest object.
 * The generate function is called to generate an XML 
 * document that can then be passed to the IDP for 
 * validation.
 * 
 * @todo - the generateXML function uses string. Need to convert it so that request
 *        - is generated using \Dom functions
 */
class AuthnRequest
{   
    private $requestType = Constants::AUTHN_REQUEST;
    private $acsUrl;
    private $issuer;
    private $ssoUrl;
    private $bindingType;
    private $signedAssertion;
    private $signedResponse;

    public function __construct($acsUrl, $issuer, $ssoUrl, $bindingType, $signedAssertion, $signedResponse)
    {
        // all values required in the authn request are set here 
        $this->acsUrl = $acsUrl;
        $this->issuer = $issuer;
        $this->destination = $ssoUrl;
        $this->bindingType = $bindingType;
        $this->signedAssertion = $signedAssertion;
        $this->signedResponse = $signedResponse;
    }

    /**
     * This function is called to generate our authnRequest. This is an internal
     * function and shouldn't be called directly. Call the @build function instead.
     * It returns the string format of the XML and encode it based on the sso
     * binding type.
     * 
     * @todo - Have to convert this so that it's not a string value but an XML document
     */
    private function generateXML()
    {
        $requestXmlStr = '<?xml version="1.0" encoding="UTF-8"?>' .
                        ' <samlp:AuthnRequest 
                                xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" 
                                xmlns="urn:oasis:names:tc:SAML:2.0:assertion" ID="' . SAMLUtilities::generateID() .
						    '"  Version="2.0" IssueInstant="' . SAMLUtilities::generateTimestamp() . '"';
        $requestXmlStr .= ' WantAssertionSigned="true"';
        $requestXmlStr .= ' WantSAMLResponseSigned="true"';
		$requestXmlStr .= '     ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" AssertionConsumerServiceURL="' . $this->acsUrl . 
                        '"      Destination="' . $this->destination . '">
                                <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">'.$this->issuer.'</saml:Issuer>
                                <samlp:NameIDPolicy AllowCreate="true" Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified"/>
                            </samlp:AuthnRequest>';
                            //var_dump($this->acsUrl, $this->destination, $this->issuer);exit;
        return $requestXmlStr;
    }


    /**
     * This function is used to build our AuthnRequest. Deflate
     * and encode the AuthnRequest string if the sso binding 
     * type is empty or is of type HTTPREDIRECT.
     */
    public function build()
    {   
        $pluginSettings=PluginSettings::getPluginSettings();
        $requestXmlStr = $this->generateXML();
        if(empty($this->bindingType) 
            || $this->bindingType == Constants::HTTP_REDIRECT)
        {
			$deflatedStr = gzdeflate($requestXmlStr);
			$base64EncodedStr = base64_encode($deflatedStr);
			$urlEncoded = urlencode($base64EncodedStr);
			$requestXmlStr = $urlEncoded;
        }
        return $requestXmlStr;
    }
}