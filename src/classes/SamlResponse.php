<?php

namespace MiniOrange\Classes;

use DOMDocument;
use DOMElement;
use MiniOrange\Helper\SAMLUtilities;

/**
 * This class takes DomElement XML as an input and converts it to
 * a SAML Response object.
 */
class SamlResponse
{

    private $assertions;
    private $destination;
    private $certificates;
    private $signatureData;
    private $statusCode;
    private $xml;

    /**
     * Response constructor.
     * @param DOMElement|NULL $xml
     * @throws \Exception
     */
    public function __construct(DOMElement $xml = NULL)
    {
        $this->assertions = array();
		$this->certificates = array();

        if ($xml === NULL) return;

        $this->xml = $xml;
		
		$sig = SAMLUtilities::validateElement($xml);
		if ($sig !== FALSE) {
			$this->certificates = $sig['Certificates'];
			$this->signatureData = $sig;
		}
        
        $doc = $xml->ownerDocument;
        $xpath = new \DOMXpath($doc);
        if(!(@$xpath->query('/saml2p:Response',$xml)))
            $status = SAMLUtilities::xpQuery($xml, './samlp:Status/samlp:StatusCode');
        else
            $status = SAMLUtilities::xpQuery($xml, './saml2p:Status/saml2p:StatusCode');

        // $status = SAMLUtilities::xpQuery($xml, './samlp:Status/samlp:StatusCode');
        $this->statusCode = $status[0]->getAttribute('Value');
		
		/* set the destination from saml response */
		if ($this->xml->hasAttribute('Destination')) {
            $this->destination = $this->xml->getAttribute('Destination');
        }
		
		for ($node = $this->xml->firstChild; $node !== NULL; $node = $node->nextSibling) {
			if ($node->namespaceURI !== 'urn:oasis:names:tc:SAML:2.0:assertion')
				continue;
			if ($node->localName === 'Assertion' || $node->localName === 'EncryptedAssertion')
				$this->assertions[] = new Assertion($node);
		}
    }

    /** Retrieve the assertions in this response.  */
    public function getAssertions()
    {
        return $this->assertions;
    }

    /** Set the assertions that should be included in this response. */
    public function setAssertions(array $assertions)
    {
        $this->assertions = $assertions;
    }

    public function getDestination()
    {
        return $this->destination;
    }

    public function getCertificates()
    {
        return $this->certificates;
    }

    public function getSignatureData()
    {
        return $this->signatureData;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getXML()
    {
        return $this->xml;
    }

    public function __toString()
    {
        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = FALSE;
        $dom->formatOutput = TRUE;
        $dom->loadXML($this->xml->ownerDocument->saveXML($this->xml));
        return $dom->saveXml();
    }
}
