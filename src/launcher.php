<?php

use MiniOrange\Helper\DB as DB;

if (!isset($_SESSION)) {
    session_start();
}
// check if the directory containing CSS,JS,Resources exists in the root folder of the site
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/miniorange/sso')) {
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

if (isset($_SESSION)) {
    if (is_user_registered() == NULL) {
        header("Location: register.php");
        exit();
    } else {
        header("Location: admin_login.php");
        exit();
    }
}

?>