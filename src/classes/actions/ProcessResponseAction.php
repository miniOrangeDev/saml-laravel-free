<?php

namespace MiniOrange\Classes\Actions;

use Exception;
use MiniOrange\Classes\SamlResponse;
use MiniOrange\Helper\Exception\InvalidAudienceException;
use MiniOrange\Helper\Exception\InvalidDestinationException;
use MiniOrange\Helper\Exception\InvalidIssuerException;
use MiniOrange\Helper\Exception\InvalidSamlStatusCodeException;
use MiniOrange\Helper\Exception\InvalidSignatureInResponseException;
use MiniOrange\Helper\Lib\XMLSecLibs\XMLSecurityKey;
use MiniOrange\Helper\PluginSettings;
use MiniOrange\Helper\SAMLUtilities;

/**
 * Handles processing of SAML Responses from the IDP. Process the SAML Response
 * from the IDP and detect if it's a valid response from the IDP. Validate the
 * certificates and the SAML attributes and Update existing user attributes
 * and groups if necessary. Log the user in.
 */
class ProcessResponseAction
{
    private $samlResponse;
    private $certfpFromPlugin;
    private $acsUrl;
    private $responseSigned;
    private $assertionSigned;
    private $issuer;
    private $spEntityId;

    private $pluginSettings;

    public function __construct(SamlResponse $samlResponseXML)
    {   
        $this->pluginSettings = PluginSettings::getPluginSettings();

        //You can use dependency injection to get any class this observer may need.
        $this->acsUrl = $this->pluginSettings->getAcsUrl();
        $this->certfpFromPlugin = XMLSecurityKey::getRawThumbprint($this->pluginSettings->getX509Certificate());
        $this->responseSigned = true;
        $this->assertionSigned = true;
        $this->issuer = $this->pluginSettings->getIdpEntityId();
        $this->spEntityId = $this->pluginSettings->getSpEntityId();


        $this->samlResponse = $samlResponseXML;
    }

    /**
     * @return mixed
     * @throws InvalidAudienceException
     * @throws InvalidDestinationException
     * @throws InvalidIssuerException
     * @throws InvalidSamlStatusCodeException
     * @throws InvalidSignatureInResponseException
     * @throws \Exception
     */
    public function execute()
    {
        $this->validateStatusCode();
        
        $responseSignatureData = $this->samlResponse->getSignatureData();
       
        $assertionSignatureData = current($this->samlResponse->getAssertions())->getSignatureData();
        
        $this->certfpFromPlugin = iconv("UTF-8", "CP1252//IGNORE", $this->certfpFromPlugin);
        
        $this->certfpFromPlugin = preg_replace('/\s+/', '', $this->certfpFromPlugin);
        
        $this->validateSignature($responseSignatureData, $assertionSignatureData);
        
        $this->validateDestinationURL();
        
        $this->validateResponseSignature($responseSignatureData);
        
        $this->validateAssertionSignature($assertionSignatureData);
        
        $this->validateIssuerAndAudience();
        
    }

    /**
     * Function checks if either of the SAML Response or
     * Assertion is signed or not
     * 
     * @param $responseSignatureData
     * @param $assertionSignatureData
     * @throws \Exception
     */
    private function validateSignature($responseSignatureData, $assertionSignatureData){
        if(!$responseSignatureData && !$assertionSignatureData){
            throw new Exception('Neither the SAML Response nor the Assertion were signed. Please make sure that your Identity Provider sign atleast one of them.');
        }
    }

    /**
     * Function checks if the signature in the Response element
     * of the SAML response is a valid response. Throw an error
     * otherwise.
     *
     * @param $responseSignatureData
     * @throws InvalidSignatureInResponseException
     * @throws \Exception
     */
    private function validateResponseSignature($responseSignatureData)
    {
        if($this->responseSigned!="1" || empty($responseSignatureData)) return;
        $validSignature = SAMLUtilities::processResponse($this->certfpFromPlugin, $responseSignatureData);
        if(!$validSignature) {
            throw new InvalidSignatureInResponseException($this->pluginSettings->getX509Certificate(),
                $responseSignatureData['Certificates'][0],$this->samlResponse->getXML());
            
        }
    }


    /**
     * Function checks if the status coming in the SAML
     * response is SUCCESS and not a responder or
     * requester
     *
     * @param $responseSignatureData
     * @throws InvalidSamlStatusCodeException
     */
    private function validateStatusCode()
    {
        $statusCode = $this->samlResponse->getStatusCode();
        if(strpos($statusCode,'Success')===false)
            throw new InvalidSamlStatusCodeException($statusCode,$this->samlResponse->getXML());
    }

    /**
     * Function checks if the signature in the Assertion element
     * of the SAML response is a valid response. Throw an error
     * otherwise.
     *
     * @param $assertionSignatureData
     * @throws InvalidSignatureInResponseException
     */
    private function validateAssertionSignature($assertionSignatureData)
    {
        if($this->assertionSigned!=TRUE || empty($assertionSignatureData)) return;
        $validSignature = SAMLUtilities::processResponse($this->certfpFromPlugin, $assertionSignatureData,
            $this->samlResponse);
        if(!$validSignature) {
            throw new InvalidSignatureInResponseException($this->pluginSettings->getX509Certificate(),
                $assertionSignatureData['Certificates'][0],$this->samlResponse->getXML());
        }
    }
    

    /**
     * Function validates the Issuer and Audience from the
     * SAML Response. THrows an error if the Issuer and
     * Audience values don't match with the one in the
     * database.
     *
     * @throws InvalidIssuerException
     * @throws InvalidAudienceException
     */
    private function validateIssuerAndAudience()
    {
        $issuer = current($this->samlResponse->getAssertions())->getIssuer();
        $audience = current(current($this->samlResponse->getAssertions())->getValidAudiences());
        //echo " $issuer is issuer from response $this->issuer was stored $audience is audience from response should be $this->spEntityId";exit;
        if(strcmp($this->issuer, $issuer) != 0)
            throw new InvalidIssuerException($this->issuer,$issuer,$this->samlResponse->getXML());
        if(strcmp($audience, $this->spEntityId) != 0)
            throw new InvalidAudienceException($this->spEntityId,$audience,$this->samlResponse->getXML());
    }


    /**
     * Function validates the Destination in the SAML Response.
     * Throws an error if the Destination doesn't match
     * with the one in the database.
     *
     * @param $currentURL
     * @throws InvalidDestinationException
     */
    private function validateDestinationURL()
    {
        $msgDestination = $this->samlResponse->getDestination();
        if ($msgDestination !== NULL && $msgDestination !== $this->acsUrl)
            throw new InvalidDestinationException($msgDestination,$this->acsUrl,$this->samlResponse);
    }
}