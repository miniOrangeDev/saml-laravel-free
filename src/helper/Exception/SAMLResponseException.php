<?php

namespace MiniOrange\Helper\Exception;

use MiniOrange\Helper\Messages;

/**
 * Exception denotes a SAMLResponseException.
 * This exception is not thrown but is 
 * extended by other exception classes.
 */
class SAMLResponseException extends \Exception
{
    private $samlResponse;
    private $isCertError;
	public function __construct($message, $code, $xml, $isCertError) 
	{
        $this->xml 	= $xml;
        $this->isCertError = $isCertError;
        parent::__construct($message, $code, NULL);
    }

    public function getSamlResponse(){ return Messages::parse('SAML_RESPONSE', array('xml'=>$this->parseXML($this->xml))); }

    public function getPluginCert(){ }

    public function getCertInResponse(){ }

    public function isCertError(){ return $this->isCertError; }

    /**
	 * This function is used to show an XML in 
	 * the plain text format for debugging 
	 * purposes.
	 */
	public static function parseXML($xml)
	{
		$dom = new \DOMDocument;
		$dom->preserveWhiteSpace = TRUE;
		$dom->formatOutput = TRUE;
		$dom->loadXML($xml->ownerDocument->saveXML($xml));
		return htmlentities($dom->saveXml());
	}
}