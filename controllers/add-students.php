<?php
@include '../models/functions.php';
// Debugger
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['submit'])) {
    $student_name = $_POST['name'];
    $student_email = $_POST['student_email'];
    $student_gender = $_POST['gender'];
    $student_password = sha1(md5('123456'));
    $user_type = 'student';

    $data = [
        'name' => $student_name,
        'email' => $student_email,
        'password' => $student_password,
        'user_type' => $user_type,
        'gender' => $student_gender
    ];

    if (insertRecord('users', $data)) {
        header('location:../views/admin/admin-students.php?msg=Student added successfully');
    }
}
