<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that IDP is not valid as it maynot 
 * have all the necessary information about a IDP
 */
class InvalidIdentityProviderException extends \Exception
{
	public function __construct() 
	{
		$message 	= Messages::parse('INVALID_IDP');
		$code 		= 119;		
        parent::__construct($message, $code, NULL);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}