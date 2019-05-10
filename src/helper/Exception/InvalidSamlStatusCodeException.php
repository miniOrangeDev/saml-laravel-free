<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that the SAML IDP sent a 
 * Responder or Requester SAML response instead
 * of Success in the 
 */
class InvalidSamlStatusCodeException extends SAMLResponseException
{
	public function __construct($statusCode,$xml) 
	{
		$message 	= Messages::parse('INVALID_INSTANT',array('statuscode'=>$statusCode));
		$code 		= 117;		
        parent::__construct($message, $code, $xml, FALSE);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}