<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../models/functions.php';

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password = sha1(md5($password));

    // Debugging statement
    error_log("Username: $username, Password: $password");

    $record = getRecord('admin', "username = '$username' AND password = '$password'");

    // Debugging statement
    error_log("Record: " . print_r($record, true));

    header('Content-Type: application/json');
    if ($record) {
        session_start(); // Make sure to start the session
        $_SESSION['username'] = $username;
        echo json_encode([
            'status' => 'success',
            'user_id' => $record['admin_id'],
            'first_name' => $record['firstname'],
            'last_name' => $record['lastname']
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing username or password']);
}
