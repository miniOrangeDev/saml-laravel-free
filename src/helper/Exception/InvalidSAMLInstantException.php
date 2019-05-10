<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that the Issue Instant in the 
 * SAML request is invalid. 
 */
class InvalidSAMLInstantException extends \Exception
{
	public function __construct() 
	{
		$message 	= Messages::parse('INVALID_INSTANT');
		$code 		= 117;		
        parent::__construct($message, $code, NULL);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}