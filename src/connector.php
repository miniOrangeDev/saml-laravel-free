<?php

use MiniOrange\Helper\DB as DB;
use MiniOrange\Helper\Lib\AESEncryption;
use Illuminate\Support\Facades\Schema;
use MiniOrange\Helper\Constants;
use MiniOrange\Classes\Actions\DatabaseController as DBinstaller;

if (!defined('MSSP_VERSION'))
    define('MSSP_VERSION', '1.0.0');
if (!defined('MSSP_NAME'))
    define('MSSP_NAME', basename(__DIR__));
if (!defined('MSSP_DIR'))
    define('MSSP_DIR', __DIR__);
if (!defined('MSSP_TEST_MODE'))
    define('MSSP_TEST_MODE', FALSE);

if (isset($_SERVER['REQUEST_URI'])) {
    if ($_SERVER['REQUEST_URI'] == '/login') {
        // for generating a login button on login page
        echo '<script>
                window.onload = function() { addSsoButton() };
                function addSsoButton() {
                var ele = document.createElement("button");
                ele.className = "btn btn-primary";
                ele.innerHTML = "Single Sign On";
                ele.name = "sso_button";
                ele.id = "sso_button";
                ele.style ="margin-left:44%";
                ele.onclick = function() {window.location.replace("/login.php")};
                document.body.appendChild(ele);
                }

                </script>';
    }
}


// recursive function to copy files within directory
function recurse_copy($src, $dst)
{
    $dir = opendir($src);
    
    @mkdir($dst, 0777, true);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function checkPasswordpattern($password)
{
    $pattern = '/^[(\w)*(\!\@\#\$\%\^\&\*\.\-\_)*]+$/';

    return !preg_match($pattern, $password);
}

function mo_saml_show_success_message()
{
    if (isset($_SESSION['show_error_msg']))
        unset($_SESSION['show_error_msg']);
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION['show_success_msg'] = 1;
}

function mo_saml_show_error_message()
{
    if (isset($_SESSION['show_success_msg']))
    unset($_SESSION['show_success_msg']);
    if (!isset($_SESSION)) {
        session_start();
    }
    $_SESSION['show_error_msg'] = 1;
}

function mo_saml_check_empty_or_null($value)
{
    if (!isset($value) || empty($value)) {
        return true;
    }
    return false;
}

function mo_saml_is_curl_installed()
{
    if (in_array('curl', get_loaded_extensions())) {
        return 1;
    } else {
        return 0;
    }
}

function is_user_registered()
{
    return DB::get_registered_user();
}

function mo_saml_is_customer_registered_saml($check_guest = true)
{
    $email = DB::get_option('mo_saml_admin_email');
    $customerKey = DB::get_option('mo_saml_admin_customer_key');
    
    if (mo_saml_is_guest_enabled() && $check_guest)
        return 1;
    if (!$email || !$customerKey || !is_numeric(trim($customerKey))) {
        return 0;
    } else {
        return 1;
    }
}

function mo_saml_is_guest_enabled()
{
    $guest_enabled = DB::get_option('mo_saml_guest_enabled');
    return $guest_enabled;
}

function mo_saml_is_customer_registered()
{
    $email = DB::get_option('mo_saml_admin_email');
    $customerKey = DB::get_option('mo_saml_admin_customer_key');
    if (!$email || !$customerKey || !is_numeric(trim($customerKey))) {
        return false;
    } else {
        return true;
    }
}

function mo_register_action()
{

    $email = $_POST['email'];
    $password = stripslashes($_POST['password']);
    $confirm_password = stripslashes($_POST['confirm_password']);
    $use_case = $_POST['use_case'];

    DB::update_option('mo_saml_admin_email', $email);
    DB::update_option('mo_saml_use_case', $use_case);
    if (strcmp($password, $confirm_password) == 0) {
        DB::update_option('mo_saml_admin_password', $password);
        $customer = new CustomerSaml();
        $content = json_decode($customer->check_customer(), true);
        $response = create_customer();
        if (strcasecmp($response['status'], 'success') == 0) {
                $customer->submit_register_user($email, $use_case);
                DB::update_option('mo_saml_message', 'Registration Successful');
                mo_saml_show_success_message();
        } else {
            DB::update_option('mo_saml_admin_email', '');
            DB::update_option('mo_saml_use_case', '');
        }
    } else {
        $response['status'] = "not_match";
        DB::update_option('mo_saml_message', 'Passwords do not match.');
        mo_saml_show_error_message();
    }

    return $response;
}

function create_customer()
{
    $customer = new CustomerSaml();
    $customerKey = json_decode($customer->create_customer(), true);
    $response = array();

    if (strcasecmp($customerKey['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS') == 0) {
        $api_response = get_current_customer();
        if ($api_response) {
            $response['status'] = "success";
        } else
            $response['status'] = "error";
    } else if (strcasecmp($customerKey['status'], 'SUCCESS') == 0) {
        DB::update_option('mo_saml_admin_customer_key', $customerKey['id']);
        DB::update_option('mo_saml_admin_api_key', $customerKey['apiKey']);
        DB::update_option('mo_saml_customer_token', $customerKey['token']);
        DB::update_option('mo_saml_admin_password', '');
        DB::update_option('mo_saml_message', 'Thank you for registering with miniorange.');
        DB::update_option('mo_saml_registration_status', '');
        DB::update_option('mo_saml_use_case', '');
        DB::delete_option('mo_saml_verify_customer');
        DB::delete_option('mo_saml_new_registration');
        $response['status'] = "success";
        return $response;
    } else{
        DB::update_option('mo_saml_message', $customerKey['status']);
        $response['status'] = "error";
    }

    DB::update_option('mo_saml_admin_password', '');
    return $response;
}

function get_current_customer()
{
    $customer = new CustomerSaml();
    $content = $customer->get_customer_key();
    
    if(strcasecmp($content, 'Invalid username or password. Please try again.') == 0){
        DB::update_option('mo_saml_message', $content);
        $_SESSION['show_error_msg'] = true;
        $response['status'] = "error";
    } else{
        $customerKey = json_decode($content, true);
        $response = array();
        if (json_last_error() == JSON_ERROR_NONE) {
            DB::update_option('mo_saml_admin_customer_key', $customerKey['id']);
            DB::update_option('mo_saml_admin_api_key', $customerKey['apiKey']);
            DB::update_option('mo_saml_customer_token', $customerKey['token']);
            DB::update_option('mo_saml_admin_password', '');
            DB::update_option('mo_saml_use_case', '');
            DB::delete_option('mo_saml_verify_customer');
            DB::delete_option('mo_saml_new_registration');
            $response['status'] = "success";
            return $response;
        } else {
    
            DB::update_option('mo_saml_message', 'You already have an account with miniOrange. Please enter a valid password.');
            mo_saml_show_error_message();
            $response['status'] = "error";
            return $response;
        }
    }
}

function mo_saml_show_customer_details()
{
    ?>
    <div class="mo_saml_table_layout">
        <h2>Thank you for registering with miniOrange.</h2>

        <table border="1"
               style="background-color: #FFFFFF; border: 1px solid #CCCCCC; border-collapse: collapse; padding: 0px 0px 0px 10px; margin: 2px; width: 85%">
            <tr>
                <td style="width: 45%; padding: 10px;">miniOrange Account Email</td>
                <td style="width: 55%; padding: 10px;"><?php echo DB::get_option('mo_saml_admin_email'); ?></td>
            </tr>
            <tr>
                <td style="width: 45%; padding: 10px;">Customer ID</td>
                <td style="width: 55%; padding: 10px;"><?php echo DB::get_option('mo_saml_admin_customer_key') ?></td>
            </tr>
        </table>
        <br/> <br/>
        <form style="display: none;" id="loginform"
              action="<?php echo DB::get_option('mo_saml_host_name') . 'moas/login'; ?>"
              target="_blank" method="post">
            <input type="email" name="username"
                   value="<?php echo DB::get_option('mo_saml_admin_email'); ?>"/> <input
                    type="text" name="redirectUrl"
                    value="<?php echo DB::get_option('mo_saml_host_name') . 'moas/initializepayment'; ?>"/>
            <input type="text" name="requestOrigin" id="requestOrigin"/>
        </form>
        <script>
            function upgradeform(planType) {
                jQuery('#requestOrigin').val(planType);
                    jQuery('#loginform').submit();
            }
        </script>
    </div>
    <?php
}

function mo_saml_remove_account()
{
    DB::delete_option('mo_saml_new_registration');
    DB::delete_option('mo_saml_admin_phone');
    DB::delete_option('mo_saml_admin_password');
    DB::delete_option('mo_saml_verify_customer');
    DB::delete_option('mo_saml_admin_customer_key');
    DB::delete_option('mo_saml_admin_api_key');
    DB::delete_option('mo_saml_customer_token');
    DB::delete_option('mo_saml_admin_email');
    DB::delete_option('mo_saml_message');
    DB::delete_option('mo_saml_registration_status');
    DB::delete_option('mo_saml_idp_config_complete');
    DB::update_option('mo_saml_new_registration', true);
}

function sanitize_certificate($certificate)
{
    $certificate = trim($certificate);
    $certificate = preg_replace("/[\r\n]+/", "", $certificate);
    $certificate = str_replace("-", "", $certificate);
    $certificate = str_replace("BEGIN CERTIFICATE", "", $certificate);
    $certificate = str_replace("END CERTIFICATE", "", $certificate);
    $certificate = str_replace(" ", "", $certificate);
    $certificate = chunk_split($certificate, 64, "\r\n");
    $certificate = "-----BEGIN CERTIFICATE-----\r\n" . $certificate . "-----END CERTIFICATE-----";
    return $certificate;
}

?>
