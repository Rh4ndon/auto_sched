<?php
include '../models/functions.php';

if (isset($_GET['id'])) {
    $GET_ID = $_GET['id'];

    // Assuming you have a function to get the user name by ID
    $user = getRecord('users', 'id =' . $GET_ID);

    $userName = $user['name'] ?? '';

    $userEmail = $user['email'] ?? '';

    $userPassword = $user['password'] ?? '';

    $userGender = $user['gender'] ?? '';

    header('Content-Type: application/json');
    if ($userName) {

        $response[] = array(
            'name' => $userName,
            'email' => $userEmail,
            'gender' => $userGender,
            'password' => $userPassword
        );
    } else {
        $response['error'] = 'User not found';
    }
}
header('Content-Type: application/json');
echo json_encode($response);
