<?php

use MiniOrange\Helper\DB as DB;

if (!isset($_SESSION)) {
    session_id("connector");
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

if (isset($_POST['option']) && $_POST['option'] == 'save_connector_settings') {
    $saml_identity_name = '';
    $idp_entity_id = '';
    $saml_login_url = '';
    $saml_login_binding_type = '';
    $saml_x509_certificate = '';
    $sp_base_url = '';
    $sp_entity_id = '';
    $acs_url = '';
    $single_logout_url = '';
    $relaystate_url = '';

    if (mo_saml_check_empty_or_null($_POST['idp_name']) || mo_saml_check_empty_or_null($_POST['saml_login_url']) || mo_saml_check_empty_or_null($_POST['idp_entity_id'])) {
        DB::update_option('mo_saml_message', 'All the fields are required. Please enter valid entries.');
        mo_saml_show_error_message();
        return;
    } else if (!preg_match("/^\w*$/", $_POST['idp_name'])) {
        DB::update_option('mo_saml_message', 'Please match the requested format for Identity Provider Name. Only alphabets, numbers and underscore is allowed.');
        mo_saml_show_error_message();
        return;
    } else {
        $saml_identity_name = trim($_POST['idp_name']);
        $saml_login_url = trim($_POST['saml_login_url']);
        if (array_key_exists('login_binding_type', $_POST))
            $saml_login_binding_type = $_POST['login_binding_type'];

        $idp_entity_id = trim($_POST['idp_entity_id']);
        $saml_x509_certificate = sanitize_certificate($_POST['x509_certificate']);

        $sp_base_url = trim($_POST['site_base_url']);
        while(substr($sp_base_url, -1) == "/"){
            $sp_base_url = substr($sp_base_url,0,-1);
        }
        $sp_entity_id = $sp_base_url.'/miniorange_php_saml_connector';
        $acs_url = $sp_base_url.'/sso.php';
        $single_logout_url = $sp_base_url.'/logout.php';
        $relaystate_url = trim($_POST['relaystate_url']);
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


        DB::update_option('saml_identity_name', $saml_identity_name);
        DB::update_option('idp_entity_id', $idp_entity_id);
        DB::update_option('saml_login_url', $saml_login_url);
        DB::update_option('saml_login_binding_type', $saml_login_binding_type);
        DB::update_option('saml_x509_certificate', $saml_x509_certificate);
        DB::update_option('sp_base_url', $sp_base_url);
        DB::update_option('sp_entity_id', $sp_entity_id);
        DB::update_option('acs_url', $acs_url);
        DB::update_option('single_logout_url', $single_logout_url);
        DB::update_option('relaystate_url', $relaystate_url);
        DB::update_option('mo_saml_message', 'Settings saved successfully.');
        mo_saml_show_success_message();
        if (empty($saml_x509_certificate)) {
            DB::update_option("mo_saml_message", 'Invalid Certificate:Please provide a certificate');
            mo_saml_show_error_message();
        }

        $saml_x509_certificate = sanitize_certificate($saml_x509_certificate);
        if (!@openssl_x509_read($saml_x509_certificate)) {
            DB::update_option('mo_saml_message', 'Invalid certificate: Please provide a valid certificate.');
            mo_saml_show_error_message();
            DB::delete_option('saml_x509_certificate');
        }
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
    
