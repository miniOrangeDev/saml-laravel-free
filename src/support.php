<?php

use MiniOrange\Helper\DB as DB;
use CustomerSaml as CustomerSaml;

if (!class_exists("DB")) {
    require_once dirname(__FILE__) . '/helper/DB.php';
}
include_once 'connector.php';
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

if (isset($_POST['option']) && $_POST['option'] == 'mo_saml_contact_us') {

    if (!mo_saml_is_curl_installed()) {
        DB::update_option('mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Query submit failed.');
        mo_saml_show_error_message();
        return;
    }

    $email = $_POST['mo_saml_contact_us_email'];
    $phone = $_POST['mo_saml_contact_us_phone'];
    $query = $_POST['mo_saml_contact_us_query'];

    if (mo_saml_check_empty_or_null($email) || mo_saml_check_empty_or_null($query)) {
        DB::update_option('mo_saml_message', 'Please fill up Email and Query fields to submit your query.');
        mo_saml_show_error_message();
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        DB::update_option('mo_saml_message', 'Please enter a valid email address.');
        mo_saml_show_error_message();
    } else {
        $customer = new CustomerSaml();
        $submited = $customer->submit_contact_us($email, $phone, $query);
        if ($submited == false) {
            DB::update_option('mo_saml_message', 'Your query could not be submitted. Please try again.');
            mo_saml_show_error_message();
        } else {
            DB::update_option('mo_saml_message', 'Thanks for getting in touch! We shall get back to you shortly.');
            mo_saml_show_success_message();
        }
    }
}
?>