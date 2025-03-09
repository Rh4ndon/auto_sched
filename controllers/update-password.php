<?php
ini_get('display_errors');
ini_set('display_errors', 1);
include '../models/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $password = $_POST['password'];
    $password = sha1(md5($password));

    $user = getRecord('users', "id = $id");

    if ($user['password'] == $password) {
        if ($user['user_type'] == 'student') {
            header('Location: ../views/student/student-home.php?warning=Password cannot be the same as the default password');
        } else if ($user['user_type'] == 'teacher') {
            header('Location: ../views/teacher/teacher-home.php?warning=Password cannot be the same as the default password');
        } else if ($user['user_type'] == 'admin') {
            header('Location: ../views/admin/admin-home.php?warning=Password cannot be the same as the default password');
        }
        exit;
    }


    $result = editRecord('users', ['password' => $password], "id = $id");
    if ($result) {
        if ($user['user_type'] == 'student') {
            header('Location: ../views/student/student-home.php?msg=Password updated successfully');
        } else if ($user['user_type'] == 'teacher') {
            header('Location: ../views/teacher/teacher-home.php?msg=Password updated successfully');
        } else if ($user['user_type'] == 'admin') {
            header('Location: ../views/admin/admin-home.php?msg=Password updated successfully');
        }
        exit;
    } else {
        if ($user['user_type'] == 'student') {
            header('Location: ../views/student/student-home.php?error=Error updating password');
        } else if ($user['user_type'] == 'teacher') {
            header('Location: ../views/teacher/teacher-home.php?error=Error updating password');
        } else if ($user['user_type'] == 'admin') {
            header('Location: ../views/admin/admin-home.php?error=Error updating password');
        }
        exit;
    }
}
