<?php

use Illuminate\Support\Facades\Response;
use MiniOrange\Helper\DB;

if (! isset($_SESSION)) {
    session_id("connector");
    session_start();
}
$data_folder = __DIR__ . '\helper\data';
if (! file_exists($data_folder))
    mkdir($data_folder);
if (isset($_POST['option']) && ! empty($_POST['option'])) {

    $email = '';
    $password = '';
    if (isset($_POST['email']) && ! empty($_POST['email']))
        $email = $_POST['email'];
    if (isset($_POST['password']) && ! empty($_POST['password']))
        $password = $_POST['password'];
    if (! empty($password)) {
        $password = sha1($password);
    }
    if ($_POST['option'] === 'register') {
        DB::register_user($email, $password);
    }
}
if (session_id() == 'connector') {
    if (is_user_registered()) {
        header('Location: admin_login.php');
        exit();
    }
}
?>
