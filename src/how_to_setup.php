<?php

use MiniOrange\Helper\DB as DB;

if (session_status() == PHP_SESSION_NONE) {
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
if (!isset($_SESSION['authorized'])) {
    header('Location: admin_login.php');
    exit();
} else {
    if ($_SESSION['authorized'] != true) {
        header('Location: admin_login.php');
    }
}

?>
