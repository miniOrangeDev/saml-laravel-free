<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that the SAML resquest or response has missing 
 * ID attribute.
 */
class MissingIDException extends \Exception
{
	public function __construct() 
	{
		$message 	= Messages::parse('MISSING_ID_FROM_RESPONSE');
		$code 		= 125;		
        parent::__construct($message, $code, NULL);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}