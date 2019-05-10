<?php 

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that the user doesn't have a valid license key
 */
class NotRegisteredException extends \Exception
{
	public function __construct() 
	{
		$message 	= Messages::parse('INVALID_LICENSE');
		$code 		= 102;		
        parent::__construct($message, $code, NULL);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}