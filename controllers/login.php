<?php
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


// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../models/functions.php';

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password = sha1(md5($password));

    // Debugging statement
    error_log("Email: $email, Password: $password");

    $record = getRecord('users', "email = '$email' AND password = '$password'");

    // Debugging statement
    error_log("Record: " . print_r($record, true));

    header('Content-Type: application/json');
    if ($record) {

        $_SESSION['id'] = $record['id'];
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $record['name'];
        $_SESSION['user_type'] = $record['user_type'];
        echo json_encode([
            'status' => 'success',
            'user_id' => $record['id'],
            'name' => $record['name'],
            'email' => $record['email'],
            'user_type' => $record['user_type']
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing email or password']);
}
