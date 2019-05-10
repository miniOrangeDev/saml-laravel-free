<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that user has not entered all the requried fields.
 */
class RequiredFieldsException extends \Exception
{
	public function __construct() 
	{
		$message 	= Messages::parse('REQUIRED_FIELDS');
		$code 		= 104;		
        parent::__construct($message, $code, NULL);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}