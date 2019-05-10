<?php

namespace MiniOrange\Helper;

class Constants
{
    //SAML Constants
    const SAML 	 			= 'SAML';
    const AUTHN_REQUEST 	= 'AuthnRequest';
    const SAML_RESPONSE 	= 'SamlResponse';
    const HTTP_REDIRECT 	= 'HttpRedirect';
    const LOGOUT_REQUEST 	= 'LogoutRequest';

    //Names
    const SP_CERT           = 'sp-certificate.crt';
    const SP_KEY            = 'sp-key.key';
    const RESOURCE_FOLDER   = 'resources';
    const TEST_RELAYSTATE   = 'testconfig';
    const SP_ALTERNATE_CERT = 'miniorange_sp_cert.crt';
    const SP_ALTERNATE_KEY  = 'miniorange_sp_priv_key.key';
    //images
    const IMAGE_RIGHT 		= 'right.png';
    const IMAGE_WRONG 		= 'wrong.png';
    const HASH              = 'aec500ad83a2aaaa7d676c56d8015509d439d56e0e1726b847197f7f089dd8ed';
    
}