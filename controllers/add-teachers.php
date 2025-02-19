<?php
@include '../models/functions.php';
// Debugger
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['submit'])) {
    $teacher_name = $_POST['name'];
    $teacher_email = $_POST['teacher_email'];
    $teacher_gender = $_POST['gender'];
    $teacher_password = sha1(md5('123456'));
    $user_type = 'teacher';

    $data = [
        'name' => $teacher_name,
        'email' => $teacher_email,
        'password' => $teacher_password,
        'user_type' => $user_type,
        'gender' => $teacher_gender
    ];

    if (insertRecord('users', $data)) {
        header('location:../views/admin/admin-teachers.php?msg=Teacher added successfully');
    }
}
