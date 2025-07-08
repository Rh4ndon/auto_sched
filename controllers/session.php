<?php
//Session
// Add this BEFORE session_start() in login.php and session.php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

//if session empty
if (!isset($_SESSION['id'])) {
    header('Location: ../index.html');
}
