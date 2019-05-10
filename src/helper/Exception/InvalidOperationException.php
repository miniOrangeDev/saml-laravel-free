<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that there was an Invalid Operation
 */
class InvalidOperationException extends \Exception
{
	public function __construct() 
	{
		$message 	= Messages::parse('INVALID_OP');
		$code 		= 105;		
        parent::__construct($message, $code, NULL);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}