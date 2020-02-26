<?php

use MiniOrange\Helper\DB as DB;

if (!isset($_SESSION)) {
    session_start();
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
if (!is_user_registered()) {
    header('Location: register.php');
    exit();
}

if (isset($_SESSION['authorized']) && !empty($_SESSION['authorized'])) {
    if ($_SESSION['authorized'] == true) {
        header('Location: licensing.php');
    }
}
if (isset($_REQUEST['option']) && $_REQUEST['option'] == 'admin_login') {

    $email = '';
    $password = '';
    if (isset($_POST['email']) && !empty($_POST['email']))
        $email = $_POST['email'];
    if (isset($_POST['password']) && !empty($_POST['password']))
        $password = $_POST['password'];
    if (!empty($password)) {
        $password = sha1($password);
    }
    $user = DB::get_registered_user();
    $password_check = '';
    $email_check = '';
    if ($user != NULL)
        if (isset($user->password))
            $password_check = $user->password;
        else {
            $_SESSION['invalid_credentials'] = true;
        }
    if ($user != NULL) {
        if (isset($user->email))
            $email_check = $user->email;
        else
            $_SESSION['invalid_credentials'] = true;
    }

    if (!empty($password_check)) {
        if ($password === $password_check) {

            if (!isset($_SESSION['authorized']) || $_SESSION['authorized'] != true) {
                $_SESSION['authorized'] = true;
            }
            $_SESSION['admin_email'] = $email;
            header('Location: licensing.php');
            exit;
        } else {
            $_SESSION['invalid_credentials'] = true;
        }
    }
}

?>
