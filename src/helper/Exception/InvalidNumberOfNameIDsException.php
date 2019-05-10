<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that the request or response has more 
 * than 1 NameID.
 */
class InvalidNumberOfNameIDsException extends \Exception
{
	public function __construct() 
	{
		$message 	= Messages::parse('INVALID_NO_OF_NAMEIDS');
		$code 		= 124;		
        parent::__construct($message, $code, NULL);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}