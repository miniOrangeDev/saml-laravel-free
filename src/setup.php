<?php
namespace MiniOrange;
include_once 'connector.php';
use MiniOrange\Helper\DB;

use DOMElement;
use DOMNode;
use DOMDocument;
use Exception;



use MiniOrange\Helper\SAMLUtilities;


class IDPMetadataReader{

    private $identityProviders;
    private $serviceProviders;

    public function __construct(DOMNode $xml = NULL){

        $this->identityProviders = array();
        $this->serviceProviders = array();
        $SAMLUtilities=new SAMLUtilities();

        $entitiesDescriptor = $SAMLUtilities->xpQuery($xml, './saml_metadata:EntitiesDescriptor');

        if(!empty($entitiesDescriptor))
            $entityDescriptors =$SAMLUtilities->xpQuery($entitiesDescriptor[0], './saml_metadata:EntityDescriptor');
        else
            $entityDescriptors = $SAMLUtilities->xpQuery($xml, './saml_metadata:EntityDescriptor');

        foreach ($entityDescriptors as $entityDescriptor) {
            $idpSSODescriptor = $SAMLUtilities->xpQuery($entityDescriptor, './saml_metadata:IDPSSODescriptor');

            if(isset($idpSSODescriptor) && !empty($idpSSODescriptor)){
                array_push($this->identityProviders,new IdentityProviders($entityDescriptor));
            }
            //TODO: add sp descriptor
        }
    }

    public function getIdentityProviders(){
        return $this->identityProviders;
    }

    public function getServiceProviders(){
        return $this->serviceProviders;
    }

}

class IdentityProviders{

    private $entityID;
    private $loginDetails;
    private $logoutDetails;
    private $signingCertificate;
    private $encryptionCertificate;
    private $signedRequest;

    public function __construct(DOMElement $xml = NULL){

        $this->loginDetails = array();
        $this->logoutDetails = array();
        $this->signingCertificate = array();
        $this->encryptionCertificate = array();

        if ($xml->hasAttribute('entityID')) {
            $this->entityID = $xml->getAttribute('entityID');
        }

        if($xml->hasAttribute('WantAuthnRequestsSigned')){
            $this->signedRequest = $xml->getAttribute('WantAuthnRequestsSigned');
        }

        $SAMLUtilities=new SAMLUtilities();

        $idpSSODescriptor =$SAMLUtilities->xpQuery($xml, './saml_metadata:IDPSSODescriptor');

        if (count($idpSSODescriptor) > 1) {
            throw new Exception('More than one <IDPSSODescriptor> in <EntityDescriptor>.');
        } elseif (empty($idpSSODescriptor)) {
            throw new Exception('Missing required <IDPSSODescriptor> in <EntityDescriptor>.');
        }
        $idpSSODescriptorEL = $idpSSODescriptor[0];

        $info = $SAMLUtilities->xpQuery($xml, './saml_metadata:Extensions');
        
        if($info)
            $this->parseInfo($idpSSODescriptorEL);
        $this->parseSSOService($idpSSODescriptorEL);
        $this->parseSLOService($idpSSODescriptorEL);
        $this->parsex509Certificate($idpSSODescriptorEL);

    }


    private function parseInfo($xml){
        $SAMLUtilities=new SAMLUtilities();
        $displayNames = $SAMLUtilities->xpQuery($xml, './mdui:UIInfo/mdui:DisplayName');
        foreach ($displayNames as $name) {
            if($name->hasAttribute('xml:lang') && $name->getAttribute('xml:lang')=="en"){
                $this->idpName = $name->textContent;
            }
        }
    }

    private function parseSSOService($xml){
        $SAMLUtilities=new SAMLUtilities();
        $ssoServices = $SAMLUtilities->xpQuery($xml, './saml_metadata:SingleSignOnService');
        foreach ($ssoServices as $ssoService) {
            $binding = str_replace("urn:oasis:names:tc:SAML:2.0:bindings:","",$ssoService->getAttribute('Binding'));
            $this->loginDetails = array_merge( 
                $this->loginDetails, 
                array($binding => $ssoService->getAttribute('Location')) 
            );
        }
    }

    private function parseSLOService($xml){
        $SAMLUtilities=new SAMLUtilities();
        $sloServices = $SAMLUtilities->xpQuery($xml, './saml_metadata:SingleLogoutService');
        foreach ($sloServices as $sloService) {
            $binding = str_replace("urn:oasis:names:tc:SAML:2.0:bindings:","",$sloService->getAttribute('Binding'));
            $this->logoutDetails = array_merge( 
                $this->logoutDetails, 
                array($binding => $sloService->getAttribute('Location')) 
            );
        }
    }

    private function parsex509Certificate($xml){
        $SAMLUtilities=new SAMLUtilities();
        foreach ( $SAMLUtilities->xpQuery($xml, './saml_metadata:KeyDescriptor') as $KeyDescriptorNode ) {
            if($KeyDescriptorNode->hasAttribute('use')){
                if($KeyDescriptorNode->getAttribute('use')=='encryption'){
                    $this->parseEncryptionCertificate($KeyDescriptorNode);
                }else{
                    $this->parseSigningCertificate($KeyDescriptorNode);
                }
            }else{
                $this->parseSigningCertificate($KeyDescriptorNode);
            }
        }
    }

    private function parseSigningCertificate($xml){
        $SAMLUtilities=new SAMLUtilities();
        $certNode = $SAMLUtilities->xpQuery($xml, './ds:KeyInfo/ds:X509Data/ds:X509Certificate');
        $certData = trim($certNode[0]->textContent);
        $certData = str_replace(array ( "\r", "\n", "\t", ' '), '', $certData);
        if(!empty($certNode))
            array_push($this->signingCertificate, $SAMLUtilities->sanitize_certificate( $certData ));
    }


    private function parseEncryptionCertificate($xml){
        $SAMLUtilities=new SAMLUtilities();
        $certNode = $SAMLUtilities->xpQuery($xml, './ds:KeyInfo/ds:X509Data/ds:X509Certificate');
        $certData = trim($certNode[0]->textContent);
        $certData = str_replace(array ( "\r", "\n", "\t", ' '), '', $certData);
        if(!empty($certNode))
            array_push($this->encryptionCertificate, $certData);
    }

    public function getEntityID(){
        return $this->entityID;
    }

    public function getLoginURL($binding){
        return $this->loginDetails[$binding];
    }

    public function getLogoutURL($binding){
        return $this->logoutDetails[$binding];
    }

    public function getLoginDetails(){
        return $this->loginDetails;
    }

    public function getLogoutDetails(){
        return $this->logoutDetails;
    }

    public function getSigningCertificate(){
        return $this->signingCertificate;
    }

    public function getEncryptionCertificate(){
        return $this->encryptionCertificate[0];
    }

    public function isRequestSigned(){
        return $this->signedRequest;
    }

}



include_once 'helper/SAMLUtilities.php';


if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['authorized'])) {
    header('Location: admin_login.php');
    exit();
} else {
    if ($_SESSION['authorized'] != true) {
        header('Location: admin_login.php');
    }
}

// Check db connection by getting registered user
try {
    $user = DB::get_registered_user();
} catch (\Exception $e) {
    $code = $e->getCode();
    $msg = $e->getMessage();
    $trace = $e->getTraceAsString();
    $env_con = getenv('DB_CONNECTION');
    $env_db = getenv('DB_DATABASE');
    $env_host = getenv('DB_HOST');
    $config = config('database.driver');
    echo nl2br("$code \n $msg  \n DB_CONNECTION : $env_con \n DB_DATABASE : $env_db \n DB_HOST : $env_host\n If the above configuration report is empty or incomplete, run <b>php artisan config:clear</b> in your command-line, check your <b>.env</b> file and please try again.  \n\nTRACE : \n $trace");
    exit;
}
if((isset($_POST['option']) && $_POST['option']=='save_metadata_file') && isset($_POST['Upload']) && (isset($_FILES['metadata_file']) && $_FILES['metadata_file']['error']==0)){

    if ( ! empty( $_FILES['metadata_file']['tmp_name'] ) ) {
                $file = @file_get_contents( $_FILES['metadata_file']['tmp_name'] );
        } 
    else {
        if(!mo_saml_is_curl_installed()){
            DB::update_option( 'mo_saml_message', 'PHP cURL extension is not installed or disabled. Cannot fetch metadata from URL.');
            mo_saml_show_error_message();
            return;
        }

    }
    if(!is_null($file))
        upload_metadata( $file);
    
}

function upload_metadata( $file) {

    
    $document          = new DOMDocument();
    if($_FILES['metadata_file']['type']!='text/xml'){
        DB::update_option( 'mo_saml_message', 'Please provide a valid metadata file.' );
            mo_saml_show_error_message();

            return;

    }
    $document->loadXML( $file );
   
    $first_child = $document->firstChild;
    if ( ! empty( $first_child ) ) {
        $metadata           = new IDPMetadataReader( $document );
        $identity_providers = $metadata->getIdentityProviders();
    
        if ( empty( $identity_providers ) && !empty( $_FILES['metadata_file']['tmp_name']) ) {
            DB::update_option( 'mo_saml_message', 'Please provide a valid metadata file.' );
            mo_saml_show_error_message();

            return;
        }
        foreach ( $identity_providers as $key => $idp ) {

            $saml_login_url = $idp->getLoginURL( 'HTTP-Redirect' );

            $saml_issuer           = $idp->getEntityID();

            DB::update_option( 'saml_login_url', $saml_login_url );

            DB::update_option( 'idp_entity_id', $saml_issuer );
            
            break;
        }
        DB::update_option( 'mo_saml_message', 'Identity Provider details saved successfully.');
        mo_saml_show_success_message();
    } else {
        if(!empty( $_FILES['metadata_file']['tmp_name']))
        {
            DB::update_option( 'mo_saml_message', 'Please provide a valid metadata file.');
            mo_saml_show_error_message();
        }
        
    }
}


if (isset($_POST['option']) && $_POST['option'] == 'save_connector_settings') {
    $idp_entity_id = '';
    $saml_login_url = '';
    $saml_login_binding_type = '';
    $sp_base_url = '';
    $sp_entity_id = '';
    $acs_url = '';
    $single_logout_url = '';

    if (mo_saml_check_empty_or_null($_POST['saml_login_url']) || mo_saml_check_empty_or_null($_POST['idp_entity_id'])) {
        DB::update_option('mo_saml_message', 'All the fields are required. Please enter valid entries.');
        mo_saml_show_error_message();
        return;
    } else {
        $saml_login_url = trim($_POST['saml_login_url']);
        if (array_key_exists('login_binding_type', $_POST))
            $saml_login_binding_type = $_POST['login_binding_type'];

        $idp_entity_id = trim($_POST['idp_entity_id']);

        $sp_base_url = trim($_POST['site_base_url']);
        while(substr($sp_base_url, -1) == "/"){
            $sp_base_url = substr($sp_base_url,0,-1);
        }
        $sp_entity_id = trim($_POST['sp_entity_id']);
        $acs_url = trim($_POST['acs_url']);
        $single_logout_url = trim($_POST['slo_url']);
        if(!filter_var($sp_base_url, FILTER_VALIDATE_URL)){
            DB::update_option('mo_saml_message', "Invalid SP Base URL");
            mo_saml_show_error_message();
            return;
        }

        if(!filter_var($acs_url, FILTER_VALIDATE_URL)){
            DB::update_option('mo_saml_message', "Invalid ACS URL");
            mo_saml_show_error_message();
            return;
        }
        if(!filter_var($single_logout_url, FILTER_VALIDATE_URL)){
            DB::update_option('mo_saml_message', "Invalid SP SLO URL");
            mo_saml_show_error_message();
            return;
        }
        if(!filter_var($saml_login_url, FILTER_VALIDATE_URL)){
            DB::update_option('mo_saml_message',"Invalid SAML Login URL");
            mo_saml_show_error_message();
        }


        DB::update_option('idp_entity_id', $idp_entity_id);
        DB::update_option('saml_login_url', $saml_login_url);
        DB::update_option('saml_login_binding_type', $saml_login_binding_type);
        DB::update_option('sp_base_url', $sp_base_url);
        DB::update_option('sp_entity_id', $sp_entity_id);
        DB::update_option('acs_url', $acs_url);
        DB::update_option('single_logout_url', $single_logout_url);
        DB::update_option('mo_saml_message', 'Settings saved successfully.');
        mo_saml_show_success_message();
    }
}

if (isset($_POST['option']) && $_POST['option'] == 'attribute_mapping') {
    if (isset($_POST['saml_am_email']) && !empty($_POST['saml_am_email'])) {
        DB::update_option('saml_am_email', 'NameID');
    }
    if (isset($_POST['saml_am_username']) && !empty($_POST['saml_am_username'])) {
        DB::update_option('saml_am_username', 'NameID');
    }
}


?>
    
