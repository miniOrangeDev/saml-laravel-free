<?php

use MiniOrange\Helper\DB as DB;

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
if (isset($_POST['option']) and $_POST['option'] == "mo_saml_register_customer") {
    mo_register_action();
}

if (isset($_POST['option']) and $_POST['option'] == "mo_saml_goto_login") {
    DB::delete_option('mo_saml_new_registration');
    DB::update_option('mo_saml_verify_customer', 'true');
}

if (isset($_POST['option']) and $_POST['option'] == "change_miniorange") {
    mo_saml_remove_account();
    DB::update_option('mo_saml_guest_enabled', true);
    DB::update_option('mo_saml_message', 'Logged out of miniOrange account');
    mo_saml_show_success_message();
    return;
}

if (isset($_POST['option']) and $_POST['option'] == "mo_saml_go_back") {
    DB::update_option('mo_saml_registration_status', '');
    DB::update_option('mo_saml_verify_customer', '');
    DB::update_option('mo_saml_new_registration', true);
    DB::delete_option('mo_saml_admin_email');
    DB::delete_option('mo_saml_admin_phone');
}

if (isset($_POST['option']) and $_POST['option'] == "mo_saml_verify_customer") { // register the admin to miniOrange

    if (!mo_saml_is_curl_installed()) {
        DB::update_option('mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Login failed.');
        mo_saml_show_error_message();

        return;
    }

    $email = '';
    $password = '';
    if (mo_saml_check_empty_or_null($_POST['email']) || mo_saml_check_empty_or_null($_POST['password'])) {
        DB::update_option('mo_saml_message', 'All the fields are required. Please enter valid entries.');
        mo_saml_show_error_message();

        return;
    } else if (checkPasswordpattern(strip_tags($_POST['password']))) {
        DB::update_option('mo_saml_message', 'Minimum 6 characters should be present. Maximum 15 characters should be present. Only following symbols (!@#.$%^&*-_) should be present.');
        mo_saml_show_error_message();
        return;
    } else {
        $email = $_POST['email'];
        $password = stripslashes(strip_tags($_POST['password']));
    }

    DB::update_option('mo_saml_admin_email', $email);
    DB::update_option('mo_saml_admin_password', $password);
    $customer = new CustomerSaml();
    $content = $customer->get_customer_key();
    $customerKey = json_decode($content, true);
    if (json_last_error() == JSON_ERROR_NONE) {
        DB::update_option('mo_saml_admin_customer_key', $customerKey['id']);
        DB::update_option('mo_saml_admin_api_key', $customerKey['apiKey']);
        DB::update_option('mo_saml_customer_token', $customerKey['token']);
        DB::update_option('mo_saml_admin_password', '');
        DB::update_option('mo_saml_message', 'Customer retrieved successfully');
        DB::update_option('mo_saml_registration_status', 'Existing User');
        DB::delete_option('mo_saml_new_registration');
        DB::delete_option('mo_saml_verify_customer');
        mo_saml_show_success_message();
    } else {
        DB::delete_option('mo_saml_admin_email', $email);
        DB::delete_option('mo_saml_admin_password', $password);
        DB::update_option('mo_saml_message', 'Invalid username or password. Please try again.');
        mo_saml_show_error_message();
    }
    DB::update_option('mo_saml_admin_password', '');
}
?>