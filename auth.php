<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function redirectToLogin() {
    header("Location: login.php");
    exit();
}

function redirectToAdminLogin() {
    header("Location: admin/login.php");
    exit();
}
?>
