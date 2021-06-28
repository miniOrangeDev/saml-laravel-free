<?php

use MiniOrange\Helper\DB;

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_POST['option']) && !empty($_POST['option'])) {

    $email = '';
    $password = '';
    if (isset($_POST['email']) && !empty($_POST['email']))
        $email = $_POST['email'];
    if (isset($_POST['password']) && !empty($_POST['password']))
        $password = $_POST['password'];
    if (!empty($password)) {
        $password = sha1($password);
    }
    if ($_POST['option'] === 'register') {
        DB::register_user($email, $password);
    }
}
if (isset($_SESSION)) {
    if (is_user_registered()) {
        header('Location: admin_login.php');
        exit();
    }
}
?>
