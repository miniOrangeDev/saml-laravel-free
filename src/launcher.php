<?php
/*
 * Plugin Name: miniOrange PHP SAML 2.0 Connector
 * Version: 11.0.0
 * Author: miniOrange
 */

if(!isset($_SESSION)) {
    session_id('connector');
    session_start();
}

if (session_id() == 'connector' || session_id() == 'attributes') {
    if (is_user_registered()==NULL) {
        header("Location: register.php"); // https://www.google.com
        exit();
    } else {
        header("Location: admin_login.php");
        exit();
    }
}

?>