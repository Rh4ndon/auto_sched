<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../models/functions.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $user_type = $_POST['user_type'];
    $password = $_POST['password'];

    $password = sha1(md5($password));

    editRecord('users', [
        'name' => $name,
        'user_type' => $user_type,
        'gender' => $gender,
        'email' => $email,
        'password' => $password
    ], 'id = ' . $id);

    header('Location: ../views/admin/admin-profile.php?msg=User updated successfully');
    exit();
}
