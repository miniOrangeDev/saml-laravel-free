<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that the Signature In the SAML
 * request is invalid.
 */
class InvalidSignatureInResponseException extends SAMLResponseException
{
    private $pluginCert;
    private $certInResponse;

	public function __construct($pluginCert,$certInResponse,$xml) 
	{
		$message 	= Messages::parse('INVALID_RESPONSE_SIGNATURE',array('cert'=>$certInResponse,'http-host'=>$_SERVER['HTTP_HOST']));
        $code 		= 120;		
        $this->pluginCert = $pluginCert;
        $this->certInResponse = $certInResponse;
        parent::__construct($message, $code, $xml, TRUE);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function getPluginCert()
    {
        return Messages::parse('FORMATTED_CERT', array('cert'=>$this->pluginCert));
    }

    public function getCertInResponse()
    {
        return Messages::parse('FORMATTED_CERT', array('cert'=>$this->certInResponse));
    }
}