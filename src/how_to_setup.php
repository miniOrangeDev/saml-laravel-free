<?php
if (session_status() == PHP_SESSION_NONE) {
    session_id("connector");
    session_start();
}

if (!isset($_SESSION['authorized'])) {
    header('Location: admin_login.php');
    exit();
}
else{
    if($_SESSION['authorized'] != true){
        header('Location: admin_login.php');
    }
}

?>
