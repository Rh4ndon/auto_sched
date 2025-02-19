<?php
include '../models/functions.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $teacher = getRecord('users', "id = $id");

    if ($teacher) {
        echo json_encode($teacher);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Teacher not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}

if (isset($_POST['submit'])) {
    $teacher_name = $_POST['name'];
    $teacher_email = $_POST['teacher_email'];
    $teacher_gender = $_POST['gender'];
    $teacher_id = $_GET['id'];


    // Check if teacher name already exists

    $existing_teacher = getRecord('users', "name = '$teacher_name' AND id != $teacher_id");

    if ($existing_teacher) {
        header('location:../views/admin/admin-teachers.php?msg=Teacher name already exists');
        exit();
    }

    $data = [
        'name' => $teacher_name,
        'email' => $teacher_email,
        'gender' => $teacher_gender

    ];

    if (editRecord('users', $data, "id = $teacher_id")) {
        header('location:../views/admin/admin-teachers.php?msg=Teacher updated successfully');
    }
}
