<?php
if (!isset($_SESSION)) {
    session_start();
}

unset($_SESSION['authorized']);

session_destroy();

header("Location: admin_login.php");
exit();
?>