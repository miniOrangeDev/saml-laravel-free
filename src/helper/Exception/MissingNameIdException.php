<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that NameID was missing from the 
 * response or request.
 */
class MissingNameIdException extends \Exception
{
	public function __construct() 
	{
		$message 	= Messages::parse('MISSING_NAMEID');
		$code 		= 126;		
        parent::__construct($message, $code, NULL);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}