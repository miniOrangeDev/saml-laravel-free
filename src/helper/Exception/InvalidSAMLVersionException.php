<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that the version in the SAML 
 * request made is Invalid.
 */
class InvalidSAMLVersionException extends SAMLResponseException
{
	public function __construct($xml) 
	{
		$message 	= Messages::parse('INVALID_SAML_VERSION');
		$code 		= 118;		
        parent::__construct($message, $code, $xml, FALSE);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}