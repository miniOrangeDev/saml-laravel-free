<?php
use MiniOrange\Helper\DB;
use MiniOrange\Helper\Lib\AESEncryption;
use Illuminate\Support\Facades\Schema;
use MiniOrange\Helper\Constants;
use MiniOrange\Classes\Actions\DatabaseController as DBinstaller;


if (! defined('MSSP_VERSION'))
    define('MSSP_VERSION', '1.0.0');
if (! defined('MSSP_NAME'))
    define('MSSP_NAME', basename(__DIR__));
if (! defined('MSSP_DIR'))
    define('MSSP_DIR', __DIR__);
if (! defined('MSSP_TEST_MODE'))
    define('MSSP_TEST_MODE', FALSE);

// check if the directory containing CSS,JS,Resources exists in the root folder of the site
if (! is_dir($_SERVER['DOCUMENT_ROOT'] . '/miniorange/sso')) {
    // copy miniorange css,js,images,etc assets to root folder of laravel app
    $file_paths_array = array(
        '/includes',
        '/resources'
    );
    foreach ($file_paths_array as $path) {
        $src = __DIR__ . $path;
        $dst = $_SERVER['DOCUMENT_ROOT'] . "/miniorange/sso" . $path;
        recurse_copy($src, $dst);
    }
}

// recursive function to copy files within directory
function recurse_copy($src, $dst)
{
    $dir = opendir($src);

    @mkdir($dst, 077, true);
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

if (isset($_SERVER['REQUEST_URI'])) {
    if ($_SERVER['REQUEST_URI'] == '/login') {
        // for generating a login button on login page
        echo '<script>
                window.onload = function() { addSsoButton() };
                function addSsoButton() {
                var ele = document.createElement("input");
                ele.type = "button";
                ele.value = "Single Sign On";
                ele.name = "sso_button";
                ele.id = "sso_button";
                ele.style ="width: fit-content;float: right;margin-right: -6%;";
                ele.onclick = function() {window.location.replace("/login.php")};
                document.body.appendChild(ele);
                var mainObj = document.getElementsByTagName("NAV")[0];

                    var childs = mainObj.childNodes;
                    childs[0].appendChild(ele);  }              
                
                </script>';
    }
}
if (isset($_SESSION['connector']))
    DB::update_option('mo_saml_host_name', 'https://auth.miniorange.com');

if (isset($_POST['option']) && $_POST['option'] == 'mo_saml_contact_us') {
    $email = $_POST['contact_us_email'];
    $phone = $_POST['contact_us_phone'];
    $query = $_POST['contact_us_query'];

    if (mo_saml_check_empty_or_null($email) || mo_saml_check_empty_or_null($query)) {
        DB::update_option('mo_saml_message', 'Please fill up Email and Query fields to submit your query.');
        mo_saml_show_error_message();
    } else if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        DB::update_option('mo_saml_message', 'Please enter a valid email address.');
        mo_saml_show_error_message();
    } else {
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
    DB::update_option('mo_saml_new_registration',true);
    DB::delete_option('mo_saml_admin_email');
    DB::delete_option('mo_saml_admin_phone');
}

if (isset($_POST['option']) and $_POST['option'] == "mo_saml_verify_customer") { // register the admin to miniOrange

    if (! mo_saml_is_curl_installed()) {
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
        $certificate = DB::get_option('saml_x509_certificate');
        if (empty($certificate)) {
            DB::update_option('mo_saml_free_version', 1);
        }
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

if (isset($_POST['option']) and $_POST['option'] == "mo_saml_contact_us_query_option") {

    if (! mo_saml_is_curl_installed()) {
        DB::update_option('mo_saml_message', 'ERROR: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP cURL extension</a> is not installed or disabled. Query submit failed.');
        mo_saml_show_error_message();

        return;
    }

    // Contact Us query
    $email = $_POST['mo_saml_contact_us_email'];
    $phone = $_POST['mo_saml_contact_us_phone'];
    $query = $_POST['mo_saml_contact_us_query'];
    $customer = new CustomerSaml();
    if (mo_saml_check_empty_or_null($email) || mo_saml_check_empty_or_null($query)) {
        DB::update_option('mo_saml_message', 'Please fill up Email and Query fields to submit your query.');
        mo_saml_show_error_message();
    } else if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        DB::update_option('mo_saml_message', 'Please enter a valid email address.');
        mo_saml_show_error_message();
    } else {
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
    } else if (! preg_match("/^\w*$/", $_POST['idp_name'])) {
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
        $sp_entity_id = trim($_POST['sp_entity_id']);
        $acs_url = trim($_POST['acs_url']);
        $single_logout_url = trim($_POST['slo_url']);
        $relaystate_url = trim($_POST['relaystate_url']);

        DB::update_option('saml_identity_name', $saml_identity_name);
        DB::update_option('idp_entity_id', $idp_entity_id);
        DB::update_option('saml_login_url', $saml_login_url);
        DB::update_option('saml_login_binding_type', $saml_login_binding_type);
        DB::update_option('saml_x509_certificate', $saml_x509_certificate);
        DB::update_option('sp_base_url', $sp_base_url);
        DB::update_option('sp_entity_id', $sp_entity_id);
        DB::update_option('acs_url', $acs_url);
        DB::update_option('single_logout_url', $single_logout_url);
        DB::update_option('relaystate_url',$relaystate_url);

        DB::update_option('mo_saml_message', 'Settings saved successfully.');
        mo_saml_show_success_message();
        if (empty($saml_x509_certificate)) {
            DB::update_option("mo_saml_message", 'Invalid Certificate:Please provide a certificate');
            mo_saml_show_error_message();
        }

        $saml_x509_certificate = sanitize_certificate($saml_x509_certificate);
        if (! @openssl_x509_read($saml_x509_certificate)) {
            DB::update_option('mo_saml_message', 'Invalid certificate: Please provide a valid certificate.');
            mo_saml_show_error_message();
            DB::delete_option('saml_x509_certificate');
        }
    }
}

if (isset($_POST['option']) && $_POST['option'] == 'attribute_mapping') {
    if (isset($_POST['saml_am_email']) && ! empty($_POST['saml_am_email'])) {
        DB::update_option('saml_am_email', 'NameID');
    }
    if (isset($_POST['saml_am_username']) && ! empty($_POST['saml_am_username'])) {
        DB::update_option('saml_am_username', 'NameID');
    }
}

function checkPasswordpattern($password)
{
    $pattern = '/^[(\w)*(\!\@\#\$\%\^\&\*\.\-\_)*]+$/';

    return ! preg_match($pattern, $password);
}

function mo_saml_show_success_message()
{
    if (isset($_SESSION['show_error_msg']))
        unset($_SESSION['show_error_msg']);
    if(!isset($_SESSION)){
    session_id('connector');
    session_start();
    }
    $_SESSION['show_success_msg'] = 1;
}

function mo_saml_show_error_message()
{
    if (isset($_SESSION['show_success_msg']))
        unset($_SESSION['show_success_msg']);
    if(!isset($_SESSION)){
    session_id('connector');
    session_start();}
    $_SESSION['show_error_msg'] = 1;
}

function mo_saml_check_empty_or_null($value)
{
    if (! isset($value) || empty($value)) {
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
    if (! $email || ! $customerKey || ! is_numeric(trim($customerKey))) {
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
    if (! $email || ! $customerKey || ! is_numeric(trim($customerKey))) {
        return false;
    } else {
        return true;
    }
}
function mo_register_action()
{

    // $user = wp_get_current_user();
    $email = $_POST['email'];
    $password = stripslashes($_POST['password']);
    $confirmPassword = stripslashes($_POST['confirmPassword']);

    DB::update_option('mo_saml_admin_email', $email);
    if (strcmp($password, $confirmPassword) == 0) {
        DB::update_option('mo_saml_admin_password', $password);
        $customer = new CustomerSaml();
        $content = json_decode($customer->check_customer(), true);
        if (strcasecmp($content['status'], 'CUSTOMER_NOT_FOUND') == 0) {

            $response = create_customer();
        } else {
            $response = get_current_customer();
        }
        DB::update_option('mo_saml_message', 'Logged in as Guest.');
        mo_saml_show_success_message();
    } else {
        $response['status'] = "not_match";
        DB::update_option('mo_saml_message', 'Passwords do not match.');
        mo_saml_show_error_message();
    }
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
        DB::update_option('mo_saml_free_version', 1);
        DB::update_option('mo_saml_admin_password', '');
        DB::update_option('mo_saml_message', 'Thank you for registering with miniorange.');
        DB::update_option('mo_saml_registration_status', '');
        DB::delete_option('mo_saml_verify_customer');
        DB::delete_option('mo_saml_new_registration');
        $response['status'] = "success";
        return $response;
    }

    DB::update_option('mo_saml_admin_password', '');
    return $response;
}
function get_current_customer()
{
    $customer = new CustomerSaml();
    $content = $customer->get_customer_key();

    $customerKey = json_decode($content, true);

    $response = array();
    if (json_last_error() == JSON_ERROR_NONE) {
        DB::update_option('mo_saml_admin_customer_key', $customerKey['id']);
        DB::update_option('mo_saml_admin_api_key', $customerKey['apiKey']);
        DB::update_option('mo_saml_customer_token', $customerKey['token']);
        DB::update_option('mo_saml_admin_password', '');
        $certificate = DB::get_option('saml_x509_certificate');
        if (empty($certificate)) {
            DB::update_option('mo_saml_free_version', 1);
        }

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

function mo_saml_show_customer_details()
{
    ?>
    <div class="mo_saml_table_layout">
        <h2>Thank you for registering with miniOrange.</h2>

        <table border="1"
               style="background-color: #FFFFFF; border: 1px solid #CCCCCC; border-collapse: collapse; padding: 0px 0px 0px 10px; margin: 2px; width: 85%">
            <tr>
                <td style="width: 45%; padding: 10px;">miniOrange Account Email</td>
                <td style="width: 55%; padding: 10px;"><?php echo DB::get_option( 'mo_saml_admin_email' ); ?></td>
            </tr>
            <tr>
                <td style="width: 45%; padding: 10px;">Customer ID</td>
                <td style="width: 55%; padding: 10px;"><?php echo DB::get_option( 'mo_saml_admin_customer_key' ) ?></td>
            </tr>
        </table>
        <br /> <br />

        <table>
            <tr>
                <td>
                    <form name="f1" method="post" action="" id="mo_saml_goto_login_form"
                          style="margin-block-end: auto;">
                        <input type="hidden" value="change_miniorange" name="option" /> <input
                            type="submit" value="Change miniOrange Account" class="btn btn-primary" />
                    </form>
                </td>
                <td>
                    <a href="#"><input type="button" class="btn btn-primary"  onclick="upgradeform('laravel_')" value="Upgrade to Premium"/></a>
                </td>
            </tr>
        </table>

        <br />
        <form style="display: none;" id="loginform"
              action="<?php echo DB::get_option( 'mo_saml_host_name' ) . '/moas/login'; ?>"
              target="_blank" method="post">
            <input type="email" name="username"
                   value="<?php echo DB::get_option( 'mo_saml_admin_email' ); ?>" /> <input
                type="text" name="redirectUrl"
                value="<?php echo DB::get_option( 'mo_saml_host_name' ) . '/moas/initializepayment'; ?>" />
            <input type="text" name="requestOrigin" id="requestOrigin" />
        </form>
        <script>
            function upgradeform(planType) {
                jQuery('#requestOrigin').val(planType);
                if(jQuery('#mo_customer_registered').val()==1)
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
function mo_saml_show_registration_page()
{
    ?>

    <form name="f" method="post" action="">
        <input type="hidden" name="option" value="mo_saml_register_customer"/>
        <div class="mo_saml_table_layout" id="registration_div">
            <h4>Register with miniOrange</h4>
            <br/>
            <h6>Why should I register?</h6>

            <div style="background: aliceblue; padding: 10px 10px 10px 10px; border-radius: 10px;">
                You should register so that in case you need help, we can help you with step by step
                instructions. We support all known IdPs - ADFS, Okta, Salesforce, Shibboleth,
                SimpleSAMLphp, OpenAM, Centrify, Ping, RSA, IBM, Oracle, OneLogin, Bitium, WSO2 etc.
                <b>You will also need a miniOrange account to upgrade to the premium version of the connector.</b> We do not store any information except the email that you will use to register with us.
            </div>
            <br/>
            <div class="col-lg-8">
                <table class="mo_saml_settings_table">
                    <tr>
                        <td><b><font color="#FF0000">*</font>Email:</b></td>
                        <td><input class="form-control" type="email" name="email"
                                   required placeholder="person@example.com"
                                   value="<?php
                                   echo DB::get_registered_user()->email == '' ? DB::get_option("mo_saml_admin_email") : DB::get_registered_user()->email;
                                   ?>
"/>
                        </td>
                    </tr>
                    <tr>&nbsp;</tr>
                    <tr>
                        <td><b><font color="#FF0000">*</font>Password:</b></td>
                        <td><input class="form-control" required type="password"
                                   name="password" placeholder="Choose your password (Min. length 6)"
                                   minlength="6" pattern="^[(\w)*(!@#$.%^&*-_)*]+$"
                                   title="Minimum 6 characters should be present. Maximum 15 characters should be present. Only following symbols (!@#.$%^&*) should be present."
                            /></td>
                    </tr>
                    <tr>
                        <td><b><font color="#FF0000">*</font>Confirm Password:</b></td>
                        <td><input class="form-control" required type="password"
                                   name="confirmPassword" placeholder="Confirm your password"
                                   minlength="6" pattern="^[(\w)*(!@#$.%^&*-_)*]+$"
                                   title="Minimum 6 characters should be present. Maximum 15 characters should be present. Only following symbols (!@#.$%^&*) should be present."

                            /></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td><br><input type="submit" name="submit" value="Register" id="register_action"
                                       class="btn btn-primary"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="button" name="mo_saml_goto_login" id="mo_saml_goto_login"
                                   value="Already have an account?" class="btn btn-primary"/>&nbsp;&nbsp;

                        </td>
                    </tr>
                </table></div>
        </div>
    </form>
    <form name="f1" method="post" action="" id="mo_saml_goto_login_form">
        <input type="hidden" name="option" value="mo_saml_goto_login"/>
    </form>
    <form name="f" method="post" action="" id="mo_saml_continue_guest">
        <input type="hidden" name="option" value="mo_continue_as_guest"/>
    </form>

    <!-- <form name="f2" method="post" action="" id="mo_saml_register_action_form">
      <input type="hidden" name="option" value="mo_saml_register_action"/>
    </form> -->

    <script>
        jQuery("#mo_saml_goto_login").click(function () {
            jQuery("#mo_saml_goto_login_form").submit();
        });
    </script>
    <?php
}
function mo_saml_show_verify_password_page()
{
    ?>
    <form name="f" method="post" action="">
        <input type="hidden" name="option" value="mo_saml_verify_customer"/>
        <div class="mo_saml_table_layout">
            <div id="toggle1" class="panel_toggle">
                <h3>Login with miniOrange</h3>
            </div>
            <div id="panel1">
                <p><b>It seems you already have an account with miniOrange. Please enter your miniOrange email
                        and password.<br/> <a target="_blank"
                                              href="https://auth.miniorange.com/moas/idp/resetpassword">Click
                            here if you forgot your password?</a></b></p>
                <br/>
                <div class="col-lg-8">
                    <table class="mo_saml_settings_table">
                        <tr>
                            <td><b><font color="#FF0000">*</font>Email:</b></td>
                            <td><input class="form-control" type="email" name="email"
                                       required placeholder="person@example.com"
                                       value="<?php
                                       echo DB::get_option("\x6d\x6f\137\x73\x61\x6d\154\x5f\x61\144\155\151\156\137\x65\155\x61\x69\x6c");
                                       ?>
"/></td>
                        </tr>
                        <tr>
                            <td><b><font color="#FF0000">*</font>Password:</b></td>
                            <td><input class="form-control" required type="password"
                                       name="password" placeholder="Enter your password"
                                       minlength="6" pattern="^[(\w)*(!@#$.%^&*-_)*]+$"
                                       title="Minimum 6 characters should be present. Maximum 15 characters should be present. Only following symbols (!@#.$%^&*) should be present."

                                /></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>
                                <input type="submit" name="submit" value="Login"
                                       class="btn btn-primary"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="button" name="mo_saml_goback" id="mo_saml_goback" value="Back"
                                       class="btn btn-primary"/>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </form>

    <form name="f" method="post" action="" id="mo_saml_goback_form">
        <input type="hidden" name="option" value="mo_saml_go_back"/>
    </form>
    <form name="f" method="post" action="" id="mo_saml_forgotpassword_form">
        <input type="hidden" name="option" value="mo_saml_forgot_password_form_option"/>
    </form>
    <script>
        jQuery("#mo_saml_goback").click(function () {
            jQuery("#mo_saml_goback_form").submit();
        });
        jQuery("a[href=\"#mo_saml_forgot_password_link\"]").click(function () {
            jQuery("#mo_saml_forgotpassword_form").submit();
        });
    </script>
    <?php
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
