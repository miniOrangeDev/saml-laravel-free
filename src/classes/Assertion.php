<?php

namespace MiniOrange\Classes;

use DOMElement;
use Exception;
use DOMText;
use MiniOrange\Helper\Lib\XMLSecLibs\XMLSecurityKey;
use MiniOrange\Helper\SAMLUtilities;
use MiniOrange\Helper\Utilities;
use MiniOrange\Helper\Exception\InvalidSAMLVersionException;
use MiniOrange\Helper\Exception\MissingIDException;
use MiniOrange\Helper\Exception\MissingIssuerValueException;
use MiniOrange\Helper\Exception\InvalidNumberOfNameIDsException;
use MiniOrange\Helper\Exception\MissingNameIdException;

/**
 * This is the Assertion Class. It reads the Assertion coming in the SAML
 * Response and creates an Assertion object. The class would throw an error
 * if the assertion doesn't pass the validation check or has some missing
 * attributes.
 *
 * @todo : Need to optimize this code further. Specifically for encryption
 */
class Assertion
{
    private $id;
    private $issueInstant;
    private $issuer;
    private $nameId;
    private $encryptedNameId;
    private $encryptedAttribute;
    private $encryptionKey;
    private $notBefore;
    private $notOnOrAfter;
    private $validAudiences;
    private $sessionNotOnOrAfter;
    private $sessionIndex;
    private $authnInstant;
    private $authnContextClassRef;
    private $authnContextDecl;
    private $authnContextDeclRef;
    private $AuthenticatingAuthority;
    private $attributes;
    private $nameFormat;
    private $signatureKey;
    private $certificates;
    private $signatureData;
    private $requiredEncAttributes;
    private $SubjectConfirmation;
    protected $wasSignedAtConstruction = FALSE;


    /**
     * Assertion constructor.
     * @param DOMElement|NULL $xml
     * @throws Exception
     * @throws InvalidSAMLVersionException
     * @throws MissingIDException
     * @throws MissingIssuerValueException
     */
    public function __construct(DOMElement $xml = NULL)
    {
        $this->id = SAMLUtilities::generateId();
        $this->issueInstant = SAMLUtilities::generateTimestamp();
        $this->issuer = '';
        $this->authnInstant = SAMLUtilities::generateTimestamp();
        $this->attributes = array();
        $this->nameFormat = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';
        $this->certificates = array();
        $this->AuthenticatingAuthority = array();
        $this->SubjectConfirmation = array();

        if ($xml === NULL) return;

		if($xml->localName === 'EncryptedAssertion')
		{
				$data = SAMLUtilities::xpQuery($xml, './xenc:EncryptedData');
				// $encryptedMethod =  SAMLUtilities::xpQuery($xml, './xenc:EncryptedData/ds:KeyInfo');
				// $method = $encryptedMethod[0]->firstChild->firstChild->getAttribute("Algorithm");
                $encryptedMethod = SAMLUtilities::xpQuery($xml, './xenc:EncryptedData/ds:KeyInfo/xenc:EncryptedKey');
                $method = '';
                if(empty($encryptedMethod)){
                    $encryptedMethod = SAMLUtilities::xpQuery($xml, './xenc:EncryptedKey/xenc:EncryptionMethod');
                    $method = $encryptedMethod[0]->getAttribute("Algorithm");
                } else {
                    $method = $encryptedMethod[0]->firstChild->getAttribute("Algorithm");
                }
                $algo = SAMLUtilities::getEncryptionAlgorithm($method);
				if (count($data) === 0) {
					throw new Exception('Missing encrypted data in <saml:EncryptedAssertion>.');
				} elseif (count($data) > 1) {
					throw new Exception('More than one encrypted data element in <saml:EncryptedAssertion>.');
				}

				$key = new XMLSecurityKey($algo, array('type'=>'private'));
                $path = file_get_contents(Utilities::getPrivateKey());
                $key->loadKey($path);

                /** @todo - Alternate Key */
                $alternateKey = new XMLSecurityKey($algo, array('type' => 'private'));
                $path =  file_get_contents(Utilities::getAlternatePrivateKey());
                $alternateKey->loadKey($path);

				$blacklist = array();
				$xml = SAMLUtilities::decryptElement($data[0], $key, $blacklist, $alternateKey);
		}

        if (!$xml->hasAttribute('ID')) throw new MissingIDException;

        $this->id = $xml->getAttribute('ID');

        if ($xml->getAttribute('Version') !== '2.0') throw new InvalidSAMLVersionException($xml);

        $this->issueInstant = SAMLUtilities::xsDateTimeToTimestamp($xml->getAttribute('IssueInstant'));

        $issuer = SAMLUtilities::xpQuery($xml, './saml_assertion:Issuer');
        if (empty($issuer)) throw new MissingIssuerValueException;
        $this->issuer = trim($issuer[0]->textContent);

        $this->parseConditions($xml);
        $this->parseAuthnStatement($xml);
        $this->parseAttributes($xml);
        $this->parseEncryptedAttributes($xml);
        $this->parseSignature($xml);
        $this->parseSubject($xml);
    }

    /**
     * Parse subject in assertion.
     *
     * @param DOMElement $xml The assertion XML element.
     * @throws Exception
     */
    private function parseSubject(\DOMElement $xml)
    {
        $subject = SAMLUtilities::xpQuery($xml, './saml_assertion:Subject');
        if (empty($subject)) return;
        elseif (count($subject) > 1) throw new Exception('More than one <saml:Subject> in <saml:Assertion>.');

        $subject = $subject[0];
        $nameId = SAMLUtilities::xpQuery($subject, './saml_assertion:NameID | ./saml_assertion:EncryptedID/xenc:EncryptedData');
        if (empty($nameId)) throw new MissingNameIdException;
        elseif (count($nameId) > 1) throw new InvalidNumberOfNameIDsException;
        $nameId = $nameId[0];

        if ($nameId->localName === 'EncryptedData') $this->encryptedNameId = $nameId;
        else $this->nameId = SAMLUtilities::parseNameId($nameId);
    }

    /**
     * Parse conditions in assertion.
     *
     * @param DOMElement $xml The assertion XML element.
     * @throws Exception
     */
    private function parseConditions(\DOMElement $xml)
    {
        $conditions = SAMLUtilities::xpQuery($xml, './saml_assertion:Conditions');
        if (empty($conditions)) return;
        elseif (count($conditions) > 1) throw new Exception('More than one <saml:Conditions> in <saml:Assertion>.');
        $conditions = $conditions[0];

        if ($conditions->hasAttribute('NotBefore')) {
            $notBefore = SAMLUtilities::xsDateTimeToTimestamp($conditions->getAttribute('NotBefore'));
            if ($this->notBefore === NULL || $this->notBefore < $notBefore) {
                $this->notBefore = $notBefore;
            }
        }
        if ($conditions->hasAttribute('NotOnOrAfter')) {
            $notOnOrAfter = SAMLUtilities::xsDateTimeToTimestamp($conditions->getAttribute('NotOnOrAfter'));
            if ($this->notOnOrAfter === NULL || $this->notOnOrAfter > $notOnOrAfter) {
                $this->notOnOrAfter = $notOnOrAfter;
            }
        }

        for ($node = $conditions->firstChild; $node !== NULL; $node = $node->nextSibling) {
            if ($node instanceof DOMText) {
                continue;
            }
            if ($node->namespaceURI !== 'urn:oasis:names:tc:SAML:2.0:assertion') {
                throw new Exception('Unknown namespace of condition: ' . var_export($node->namespaceURI, TRUE));
            }
            switch ($node->localName) {
                case 'AudienceRestriction':
                    $audiences = SAMLUtilities::extractStrings($node, 'urn:oasis:names:tc:SAML:2.0:assertion', 'Audience');
                    if ($this->validAudiences === NULL) {
                        /* The first (and probably last) AudienceRestriction element. */
                        $this->validAudiences = $audiences;

                    } else {
                        /*
                         * The set of AudienceRestriction are ANDed together, so we need
                         * the subset that are present in all of them.
                         */
                        $this->validAudiences = array_intersect($this->validAudiences, $audiences);
                    }
                    break;
                case 'OneTimeUse':
                    /* Currently ignored. */
                    break;
                case 'ProxyRestriction':
                    /* Currently ignored. */
                    break;
                default:
                    throw new Exception('Unknown condition: ' . var_export($node->localName, TRUE));
            }
        }

    }

    /**
     * Parse AuthnStatement in assertion.
     *
     * @param DOMElement $xml The assertion XML element.
     * @throws Exception
     */
    private function parseAuthnStatement(\DOMElement $xml)
    {
        $authnStatements = SAMLUtilities::xpQuery($xml, './saml_assertion:AuthnStatement');
        if (empty($authnStatements)) {
            $this->authnInstant = NULL;

            return;
        } elseif (count($authnStatements) > 1) {
            throw new Exception('More that one <saml:AuthnStatement> in <saml:Assertion> not supported.');
        }
        $authnStatement = $authnStatements[0];

        if (!$authnStatement->hasAttribute('AuthnInstant')) {
            throw new Exception('Missing required AuthnInstant attribute on <saml:AuthnStatement>.');
        }
        $this->authnInstant = SAMLUtilities::xsDateTimeToTimestamp($authnStatement->getAttribute('AuthnInstant'));

        if ($authnStatement->hasAttribute('SessionNotOnOrAfter')) {
            $this->sessionNotOnOrAfter = SAMLUtilities::xsDateTimeToTimestamp($authnStatement->getAttribute('SessionNotOnOrAfter'));
        }

        if ($authnStatement->hasAttribute('SessionIndex')) {
            $this->sessionIndex = $authnStatement->getAttribute('SessionIndex');
        }

        $this->parseAuthnContext($authnStatement);
    }

    /**
     * Parse AuthnContext in AuthnStatement.
     *
     * @param DOMElement $authnStatementEl
     * @throws Exception
     */
    private function parseAuthnContext(\DOMElement $authnStatementEl)
    {
        // Get the AuthnContext element
        $authnContexts = SAMLUtilities::xpQuery($authnStatementEl, './saml_assertion:AuthnContext');
        if (count($authnContexts) > 1) {
            throw new Exception('More than one <saml:AuthnContext> in <saml:AuthnStatement>.');
        } elseif (empty($authnContexts)) {
            throw new Exception('Missing required <saml:AuthnContext> in <saml:AuthnStatement>.');
        }
        $authnContextEl = $authnContexts[0];

        // Get the AuthnContextDeclRef (if available)
        $authnContextDeclRefs = SAMLUtilities::xpQuery($authnContextEl, './saml_assertion:AuthnContextDeclRef');
        if (count($authnContextDeclRefs) > 1) {
            throw new Exception(
                'More than one <saml:AuthnContextDeclRef> found?'
            );
        } elseif (count($authnContextDeclRefs) === 1) {
            $this->setAuthnContextDeclRef(trim($authnContextDeclRefs[0]->textContent));
        }

        // Get the AuthnContextDecl (if available)
        $authnContextDecls = SAMLUtilities::xpQuery($authnContextEl, './saml_assertion:AuthnContextDecl');
        if (count($authnContextDecls) > 1) {
            throw new Exception(
                'More than one <saml:AuthnContextDecl> found?'
            );
        } elseif (count($authnContextDecls) === 1) {
            $this->setAuthnContextDecl(new SAML2_XML_Chunk($authnContextDecls[0]));
        }

        // Get the AuthnContextClassRef (if available)
        $authnContextClassRefs = SAMLUtilities::xpQuery($authnContextEl, './saml_assertion:AuthnContextClassRef');
        if (count($authnContextClassRefs) > 1) {
            throw new Exception('More than one <saml:AuthnContextClassRef> in <saml:AuthnContext>.');
        } elseif (count($authnContextClassRefs) === 1) {
            $this->setAuthnContextClassRef(trim($authnContextClassRefs[0]->textContent));
        }

        // Constraint from XSD: MUST have one of the three
        if (empty($this->authnContextClassRef) && empty($this->authnContextDecl) && empty($this->authnContextDeclRef)) {
            throw new Exception(
                'Missing either <saml:AuthnContextClassRef> or <saml:AuthnContextDeclRef> or <saml:AuthnContextDecl>'
            );
        }

        $this->AuthenticatingAuthority = SAMLUtilities::extractStrings(
            $authnContextEl,
            'urn:oasis:names:tc:SAML:2.0:assertion',
            'AuthenticatingAuthority'
        );
    }

    /**
     * Parse attribute statements in assertion.
     *
     * @param DOMElement $xml The XML element with the assertion.
     * @throws Exception
     */
    private function parseAttributes(\DOMElement $xml)
    {
        $firstAttribute = TRUE;
        $attributes = SAMLUtilities::xpQuery($xml, './saml_assertion:AttributeStatement/saml_assertion:Attribute');
        foreach ($attributes as $attribute) {
            if (!$attribute->hasAttribute('Name')) {
                throw new Exception('Missing name on <saml:Attribute> element.');
            }
            $name = $attribute->getAttribute('Name');

            if ($attribute->hasAttribute('NameFormat')) {
                $nameFormat = $attribute->getAttribute('NameFormat');
            } else {
                $nameFormat = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';
            }

            if ($firstAttribute) {
                $this->nameFormat = $nameFormat;
                $firstAttribute = FALSE;
            } else {
                if ($this->nameFormat !== $nameFormat) {
                    $this->nameFormat = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';
                }
            }

            if (!array_key_exists($name, $this->attributes)) {
                $this->attributes[$name] = array();
            }

            $values = SAMLUtilities::xpQuery($attribute, './saml_assertion:AttributeValue');
            foreach ($values as $value) {
                $this->attributes[$name][] = trim($value->textContent);
            }
        }
    }

    /**
     * Parse encrypted attribute statements in assertion.
     *
     * @param DOMElement $xml The XML element with the assertion.
     */
    private function parseEncryptedAttributes(\DOMElement $xml)
    {
        $this->encryptedAttribute = SAMLUtilities::xpQuery(
            $xml,
            './saml_assertion:AttributeStatement/saml_assertion:EncryptedAttribute'
        );
    }

    /**
     * @param DOMElement $xml
     * @throws Exception
     */
    private function parseSignature(\DOMElement $xml)
    {
        /* Validate the signature element of the message. */
        $sig = SAMLUtilities::validateElement($xml);
        if ($sig !== FALSE) {
            $this->wasSignedAtConstruction = TRUE;
            $this->certificates = $sig['Certificates'];
            $this->signatureData = $sig;
        }
    }


    /**
     * Validate this assertion against a public key.
     *
     * If no signature was present on the assertion, we will return FALSE.
     * Otherwise, TRUE will be returned. An exception is thrown if the
     * signature validation fails.
     *
     * @param XMLSecurityKey $key
     * @return bool
     * @throws Exception
     */
    public function validate(XMLSecurityKey $key)
    {
        if ($this->signatureData === NULL) {
            return FALSE;
        }

        SAMLUtilities::validateSignature($this->signatureData, $key);

        return TRUE;
    }

    /**
     * Retrieve the identifier of this assertion.
     *
     * @return string The identifier of this assertion.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the identifier of this assertion.
     *
     * @param string $id The new identifier of this assertion.
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Retrieve the issue timestamp of this assertion.
     *
     * @return int The issue timestamp of this assertion, as an UNIX timestamp.
     */
    public function getIssueInstant()
    {
        return $this->issueInstant;
    }

    /**
     * Set the issue timestamp of this assertion.
     *
     * @param int $issueInstant The new issue timestamp of this assertion, as an UNIX timestamp.
     */
    public function setIssueInstant($issueInstant)
    {
        $this->issueInstant = $issueInstant;
    }

    /**
     * Retrieve the issuer if this assertion.
     *
     * @return string The issuer of this assertion.
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * Set the issuer of this message.
     *
     * @param string $issuer The new issuer of this assertion.
     */
    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;
    }

    /**
     * Retrieve the NameId of the subject in the assertion.
     *
     * The returned NameId is in the format used by SAMLUtilities::addNameId().
     *
     * @see SAMLUtilities::addNameId()
     * @return array|NULL The name identifier of the assertion.
     * @throws Exception
     */
    public function getNameId()
    {
        if ($this->encryptedNameId !== NULL) {
            throw new Exception('Attempted to retrieve encrypted NameID without decrypting it first.');
        }

        return $this->nameId;
    }

    /**
     * Set the NameId of the subject in the assertion.
     *
     * The NameId must be in the format accepted by SAMLUtilities::addNameId().
     *
     * @see SAMLUtilities::addNameId()
     * @param array|NULL $nameId The name identifier of the assertion.
     */
    public function setNameId($nameId)
    {
        $this->nameId = $nameId;
    }

    /**
     * Check whether the NameId is encrypted.
     *
     * @return TRUE if the NameId is encrypted, FALSE if not.
     */
    public function isNameIdEncrypted()
    {
        if ($this->encryptedNameId !== NULL) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @param XMLSecurityKey $key
     * @param array $blacklist
     * @throws Exception
     */
    public function decryptNameId(XMLSecurityKey $key, array $blacklist = array())
    {
        if ($this->encryptedNameId === NULL) {
            /* No NameID to decrypt. */
            return;
        }

        $nameId = SAMLUtilities::decryptElement($this->encryptedNameId, $key, $blacklist);
        SAMLUtilities::getContainer()->debugMessage($nameId, 'decrypt');
        $this->nameId = SAMLUtilities::parseNameId($nameId);

        $this->encryptedNameId = NULL;
    }

    /**
     * Decrypt the assertion attributes.
     *
     * @param XMLSecurityKey $key
     * @param array $blacklist
     * @throws Exception
     */
    public function decryptAttributes(XMLSecurityKey $key, array $blacklist = array())
    {
        if ($this->encryptedAttribute === NULL) {
            return;
        }
        $firstAttribute = TRUE;
        $attributes = $this->encryptedAttribute;
        foreach ($attributes as $attributeEnc) {
            /*Decrypt node <EncryptedAttribute>*/
            $attribute = SAMLUtilities::decryptElement(
                $attributeEnc->getElementsByTagName('EncryptedData')->item(0),
                $key,
                $blacklist
            );

            if (!$attribute->hasAttribute('Name')) {
                throw new Exception('Missing name on <saml:Attribute> element.');
            }
            $name = $attribute->getAttribute('Name');

            if ($attribute->hasAttribute('NameFormat')) {
                $nameFormat = $attribute->getAttribute('NameFormat');
            } else {
                $nameFormat = 'urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified';
            }

            if ($firstAttribute) {
                $this->nameFormat = $nameFormat;
                $firstAttribute = FALSE;
            } else {
                if ($this->nameFormat !== $nameFormat) {
                    $this->nameFormat = 'urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified';
                }
            }

            if (!array_key_exists($name, $this->attributes)) {
                $this->attributes[$name] = array();
            }

            $values = SAMLUtilities::xpQuery($attribute, './saml_assertion:AttributeValue');
            foreach ($values as $value) {
                $this->attributes[$name][] = trim($value->textContent);
            }
        }
    }

    /**
     * Retrieve the earliest timestamp this assertion is valid.
     *
     * This function returns NULL if there are no restrictions on how early the
     * assertion can be used.
     *
     * @return int|NULL The earliest timestamp this assertion is valid.
     */
    public function getNotBefore()
    {
        return $this->notBefore;
    }

    /**
     * Set the earliest timestamp this assertion can be used.
     *
     * Set this to NULL if no limit is required.
     *
     * @param int|NULL $notBefore The earliest timestamp this assertion is valid.
     */
    public function setNotBefore($notBefore)
    {
        $this->notBefore = $notBefore;
    }

    /**
     * Retrieve the expiration timestamp of this assertion.
     *
     * This function returns NULL if there are no restrictions on how
     * late the assertion can be used.
     *
     * @return int|NULL The latest timestamp this assertion is valid.
     */
    public function getNotOnOrAfter()
    {
        return $this->notOnOrAfter;
    }

    /**
     * Set the expiration timestamp of this assertion.
     *
     * Set this to NULL if no limit is required.
     *
     * @param int|NULL $notOnOrAfter The latest timestamp this assertion is valid.
     */
    public function setNotOnOrAfter($notOnOrAfter)
    {
        $this->notOnOrAfter = $notOnOrAfter;
    }

    /**
     * Set $EncryptedAttributes if attributes will send encrypted
     *
     * @param boolean $ea TRUE to encrypt attributes in the assertion.
     */
    public function setEncryptedAttributes($ea)
    {
        $this->requiredEncAttributes = $ea;
    }

    /**
     * Retrieve the audiences that are allowed to receive this assertion.
     *
     * This may be NULL, in which case all audiences are allowed.
     *
     * @return array|NULL The allowed audiences.
     */
    public function getValidAudiences()
    {
        return $this->validAudiences;
    }

    /**
     * Set the audiences that are allowed to receive this assertion.
     *
     * This may be NULL, in which case all audiences are allowed.
     *
     * @param array|NULL $validAudiences The allowed audiences.
     */
    public function setValidAudiences(array $validAudiences = NULL)
    {
        $this->validAudiences = $validAudiences;
    }

    /**
     * Retrieve the AuthnInstant of the assertion.
     *
     * @return int|NULL The timestamp the user was authenticated, or NULL if the user isn't authenticated.
     */
    public function getAuthnInstant()
    {
        return $this->authnInstant;
    }


    /**
     * Set the AuthnInstant of the assertion.
     *
     * @param int|NULL $authnInstant Timestamp the user was authenticated, or NULL if we don't want an AuthnStatement.
     */
    public function setAuthnInstant($authnInstant)
    {
        $this->authnInstant = $authnInstant;
    }

    /**
     * Retrieve the session expiration timestamp.
     *
     * This function returns NULL if there are no restrictions on the
     * session lifetime.
     *
     * @return int|NULL The latest timestamp this session is valid.
     */
    public function getSessionNotOnOrAfter()
    {
        return $this->sessionNotOnOrAfter;
    }

    /**
     * Set the session expiration timestamp.
     *
     * Set this to NULL if no limit is required.
     *
     * @param int|NULL $sessionNotOnOrAfter The latest timestamp this session is valid.
     */
    public function setSessionNotOnOrAfter($sessionNotOnOrAfter)
    {
        $this->sessionNotOnOrAfter = $sessionNotOnOrAfter;
    }

    /**
     * Retrieve the session index of the user at the IdP.
     *
     * @return string|NULL The session index of the user at the IdP.
     */
    public function getSessionIndex()
    {
        return $this->sessionIndex;
    }

    /**
     * Set the session index of the user at the IdP.
     *
     * Note that the authentication context must be set before the
     * session index can be inluded in the assertion.
     *
     * @param string|NULL $sessionIndex The session index of the user at the IdP.
     */
    public function setSessionIndex($sessionIndex)
    {
        $this->sessionIndex = $sessionIndex;
    }

    /**
     * Retrieve the authentication method used to authenticate the user.
     *
     * This will return NULL if no authentication statement was
     * included in the assertion.
     *
     * Note that this returns either the AuthnContextClassRef or the AuthnConextDeclRef, whose definition overlaps
     * but is slightly different (consult the specification for more information).
     * This was done to work around an old bug of Shibboleth ( https://bugs.internet2.edu/jira/browse/SIDP-187 ).
     * Should no longer be required, please use either getAuthnConextClassRef or getAuthnContextDeclRef.
     *
     * @deprecated use getAuthnContextClassRef
     * @return string|NULL The authentication method.
     */
    public function getAuthnContext()
    {
        if (!empty($this->authnContextClassRef)) {
            return $this->authnContextClassRef;
        }
        if (!empty($this->authnContextDeclRef)) {
            return $this->authnContextDeclRef;
        }
        return NULL;
    }

    /**
     * Set the authentication method used to authenticate the user.
     *
     * If this is set to NULL, no authentication statement will be
     * included in the assertion. The default is NULL.
     *
     * @deprecated use setAuthnContextClassRef
     * @param string|NULL $authnContext The authentication method.
     */
    public function setAuthnContext($authnContext)
    {
        $this->setAuthnContextClassRef($authnContext);
    }

    /**
     * Retrieve the authentication method used to authenticate the user.
     *
     * This will return NULL if no authentication statement was
     * included in the assertion.
     *
     * @return string|NULL The authentication method.
     */
    public function getAuthnContextClassRef()
    {
        return $this->authnContextClassRef;
    }

    /**
     * Set the authentication method used to authenticate the user.
     *
     * If this is set to NULL, no authentication statement will be
     * included in the assertion. The default is NULL.
     *
     * @param string|NULL $authnContextClassRef The authentication method.
     */
    public function setAuthnContextClassRef($authnContextClassRef)
    {
        $this->authnContextClassRef = $authnContextClassRef;
    }

    /**
     * Set the authentication context declaration.
     *
     * @param \SAML2_XML_Chunk $authnContextDecl
     * @throws Exception
     */
    public function setAuthnContextDecl(SAML2_XML_Chunk $authnContextDecl)
    {
        if (!empty($this->authnContextDeclRef)) {
            throw new Exception(
                'AuthnContextDeclRef is already registered! May only have either a Decl or a DeclRef, not both!'
            );
        }

        $this->authnContextDecl = $authnContextDecl;
    }

    /**
     * Get the authentication context declaration.
     *
     * See:
     * @url http://docs.oasis-open.org/security/saml/v2.0/saml-authn-context-2.0-os.pdf
     *
     * @return \SAML2_XML_Chunk|NULL
     */
    public function getAuthnContextDecl()
    {
        return $this->authnContextDecl;
    }

    /**
     * Set the authentication context declaration reference.
     *
     * @param string $authnContextDeclRef
     * @throws Exception
     */
    public function setAuthnContextDeclRef($authnContextDeclRef)
    {
        if (!empty($this->authnContextDecl)) {
            throw new Exception(
                'AuthnContextDecl is already registered! May only have either a Decl or a DeclRef, not both!'
            );
        }

        $this->authnContextDeclRef = $authnContextDeclRef;
    }

    /**
     * Get the authentication context declaration reference.
     * URI reference that identifies an authentication context declaration.
     *
     * The URI reference MAY directly resolve into an XML document containing the referenced declaration.
     *
     * @return string
     */
    public function getAuthnContextDeclRef()
    {
        return $this->authnContextDeclRef;
    }

    /**
     * Retrieve the AuthenticatingAuthority.
     *
     *
     * @return array
     */
    public function getAuthenticatingAuthority()
    {
        return $this->AuthenticatingAuthority;
    }

    /**
     * Set the AuthenticatingAuthority
     *
     *
     * @param array.
     */
    public function setAuthenticatingAuthority($authenticatingAuthority)
    {
        $this->AuthenticatingAuthority = $authenticatingAuthority;
    }

    /**
     * Retrieve all attributes.
     *
     * @return array All attributes, as an associative array.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Replace all attributes.
     *
     * @param array $attributes All new attributes, as an associative array.
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Retrieve the NameFormat used on all attributes.
     *
     * If more than one NameFormat is used in the received attributes, this
     * returns the unspecified NameFormat.
     *
     * @return string The NameFormat used on all attributes.
     */
    public function getAttributeNameFormat()
    {
        return $this->nameFormat;
    }

    /**
     * Set the NameFormat used on all attributes.
     *
     * @param string $nameFormat The NameFormat used on all attributes.
     */
    public function setAttributeNameFormat($nameFormat)
    {
        $this->nameFormat = $nameFormat;
    }

    /**
     * Retrieve the SubjectConfirmation elements we have in our Subject element.
     *
     * @return array Array of SAML2_XML_saml_SubjectConfirmation elements.
     */
    public function getSubjectConfirmation()
    {
        return $this->SubjectConfirmation;
    }

    /**
     * Set the SubjectConfirmation elements that should be included in the assertion.
     *
     * @param array $SubjectConfirmation Array of SAML2_XML_saml_SubjectConfirmation elements.
     */
    public function setSubjectConfirmation(array $SubjectConfirmation)
    {
        $this->SubjectConfirmation = $SubjectConfirmation;
    }

    /**
     * Retrieve the private key we should use to sign the assertion.
     *
     * @return XMLSecurityKey|NULL The key, or NULL if no key is specified.
     */
    public function getSignatureKey()
    {
        return $this->signatureKey;
    }

    /**
     * Set the private key we should use to sign the assertion.
     *
     * If the key is NULL, the assertion will be sent unsigned.
     *
     * @param XMLSecurityKey|NULL $signatureKey
     */
    public function setSignatureKey(XMLsecurityKey $signatureKey = NULL)
    {
        $this->signatureKey = $signatureKey;
    }

    /**
     * Return the key we should use to encrypt the assertion.
     *
     * @return XMLSecurityKey|NULL The key, or NULL if no key is specified..
     *
     */
    public function getEncryptionKey()
    {
        return $this->encryptionKey;
    }

    /**
     * Set the private key we should use to encrypt the attributes.
     *
     * @param XMLSecurityKey|NULL $Key
     */
    public function setEncryptionKey(XMLSecurityKey $Key = NULL)
    {
        $this->encryptionKey = $Key;
    }

    /**
     * Set the certificates that should be included in the assertion.
     *
     * The certificates should be strings with the PEM encoded data.
     *
     * @param array $certificates An array of certificates.
     */
    public function setCertificates(array $certificates)
    {
        $this->certificates = $certificates;
    }

    /**
     * Retrieve the certificates that are included in the assertion.
     *
     * @return array An array of certificates.
     */
    public function getCertificates()
    {
        return $this->certificates;
    }

    public function getSignatureData()
    {
        return $this->signatureData;
    }

    /**
     * @return bool
     */
    public function getWasSignedAtConstruction()
    {
        return $this->wasSignedAtConstruction;
    }
}
