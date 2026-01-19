<?php
session_start();

//admin_login.php

// Password admin (ganti dengan password yang Anda inginkan)
define('ADMIN_PASSWORD', 'admin123');

if (isset($_POST['admin_login'])) {
    $password = $_POST['admin_password'];
    
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_page.php');
        exit();
    } else {
        $_SESSION['admin_error'] = 'Invalid admin password!';
        $_SESSION['active_form'] = 'admin';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
?>