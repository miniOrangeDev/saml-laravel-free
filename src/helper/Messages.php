<?php

namespace MiniOrange\Helper;

/**
 * This class lists down all of our messages to be shown to the admin or
 * in the frontend. This is a constant file listing down all of our 
 * constants. Has a parse function to parse and replace any dynamic 
 * values needed to be inputed in the string. Key is usually of the form
 * {{key}}
 */
class Messages
{
    //General Flow Messages
    const ERROR_OCCURRED 				= 'An error occured while processing your request. Please try again.';

    //Licensing Messages
    const INVALID_LICENSE 				= 'Invalid domain or credentials.';

    //cURL Error
    const CURL_ERROR 					= 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> 
                                            is not installed or disabled. Query submit failed.';

    //Save Settings Error
    const ISSUER_EXISTS 				= 'You seem to already have an Identity Provider for that issuer configured under : <i>{{name}}</i>';
    const NO_IDP_CONFIG					= 'Please check and make sure your <a href=setup.php>Plugin Settings</a> are configured properly.';

    const SETTINGS_SAVED				= 'Settings saved successfully.';
    const IDP_DELETED 					= 'Identity Provider settings deleted successfully.';
    const SP_ENTITY_ID_CHANGED 		    = 'SP Entity ID changed successfully.';
    const SP_ENTITY_ID_NULL			    = 'SP EntityID/Issuer cannot be NULL.';
    

    //SAML SSO Error Messages
    const INVALID_INSTANT 		        = '<strong>INVALID_REQUEST: </strong>Request time is greater than the current time.<br/>';
    const INVALID_SAML_VERSION 			= 'We only support SAML 2.0! Please send a SAML 2.0 request.<br/>';
    const INVALID_IDP 					= '<strong>INVALID_IDP: </strong>No Identity Provider configuration found. Please configure your 
                                            Identity Provider.<br/>';
    const INVALID_RESPONSE_SIGNATURE 	= '<strong>INVALID_SIGNATURE: Configure your X-509 Certificate at {{http-host}}/setup.php to be the one shown below and please try again.</strong><br><p class="error-cert">-----BEGIN CERTIFICATE-----<br>{{cert}}<br>-----END CERTIFICATE-----</p>';
    const SAML_INVALID_OPERATION 		= '<strong>INVALID_OPERATION: </strong>Invalid Operation! Please contact your site administrator.<br/>';
    const MISSING_NAMEID 				= 'Missing <saml:NameID> or <saml:EncryptedID> in <saml:Subject>.';
    const INVALID_NO_OF_NAMEIDS 		= 'More than one <saml:NameID> or <saml:EncryptedD> in <saml:Subject>.';
    const MISSING_ID_FROM_RESPONSE 		= 'Missing ID attribute on SAML assertion.';
    const MISSING_ISSUER_VALUE 			= 'Missing <saml:Issuer> in assertion.';
    const INVALID_ISSUER                = 'IDP Entity ID mismatch. <strong>Please configure {{found}} as Entity ID at {{http-host}}/setup.php</strong>';
    const INVALID_AUDIENCE              = 'Invalid audience URI. Expected {{expect}}, found {{found}}';
    const INVALID_DESTINATION           = 'Destination in response doesn\'t match the current URL. Destination is {{destination}}, 
                                            current URL is {{currenturl}}.';
    const MISSING_ATTRIBUTES_EXCEPTION  = 'SAML Response doesn\'t have the necessary attributes to log the user in';
    const INVALID_STATUS_CODE           = '<strong>INVALID_STATUS_CODE: </strong> The Identity Provider returned an Invalid response. 
                                            Identity Provider has sent {{statuscode}} status code in SAML Response.
                                            Please check with your Identity Provider for more information.';
    const MISSING_SAML_RESPONSE         = 'No Valid Response Found';
    const SAML_RESPONSE                 = "<pre>{{xml}}</pre>";
    const FORMATTED_CERT                = "<pre>{{cert}}</pre>";
    const INVALID_CERTIFICATE_FORMAT    = "The x509 certificate configured in the connector is not in the correct format";


    /**
     * Parse the message
     * @param $message
     * @param array $data
     * @return mixed
     */
    public static function parse($message , $data=array())
    {
        $message = constant( "self::".$message );
        foreach($data as $key => $value)
        {
            $message = str_replace("{{" . $key . "}}", $value , $message);
        }
        return $message;
    }
}