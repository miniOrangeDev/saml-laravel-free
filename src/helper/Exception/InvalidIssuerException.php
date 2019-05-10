<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that Issuer in the SAML response
 * doesn't match the one set by the plugin
 */
class InvalidIssuerException extends SAMLResponseException
{
	public function __construct($expect,$found,$xml) 
	{
		$message 	= Messages::parse('INVALID_ISSUER',array('found'=>$found,'http-host'=>$_SERVER['HTTP_HOST']));
		$code 		= 101;		
        parent::__construct($message, $code, $xml, FALSE);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}