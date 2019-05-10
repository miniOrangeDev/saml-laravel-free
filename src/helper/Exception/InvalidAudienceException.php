<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that the audience value in
 * the SAML response doesn't match the one set
 * by the plugin
 */
class InvalidAudienceException extends SAMLResponseException
{
	public function __construct($expect,$found,$xml) 
	{
		$message 	= Messages::parse('INVALID_AUDIENCE',array('expect'=>$expect,'found'=>$found));
		$code 		= 108;		
        parent::__construct($message, $code, $xml, FALSE);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}