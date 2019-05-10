<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that the destination value
 * in the SAML Response doesn't match the one
 * set by the plugin.
 */
class InvalidDestinationException extends SAMLResponseException
{
	public function __construct($destination,$currenturl,$xml) 
	{
		$message 	= Messages::parse('INVALID_DESTINATION',array('destination'=>$destination,'currenturl'=>$currenturl));
		$code 		= 108;		
        parent::__construct($message, $code, $xml, FALSE);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}