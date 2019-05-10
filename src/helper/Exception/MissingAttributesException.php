<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes that the SAML resquest or response has missing 
 * ID attribute.
 */
class MissingAttributesException extends \Exception
{
	public function __construct() 
	{
		$message 	= Messages::parse('MISSING_ATTRIBUTES_EXCEPTION');
		$code 		= 125;		
        parent::__construct($message, $code, NULL);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}