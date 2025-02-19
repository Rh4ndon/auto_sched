<?php
include '../models/functions.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $student = getRecord('users', "id = $id");

    if ($student) {
        echo json_encode($student);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}

if (isset($_POST['submit'])) {
    $student_name = $_POST['name'];
    $student_email = $_POST['student_email'];
    $student_gender = $_POST['gender'];
    $student_id = $_GET['id'];


    // Check if student name already exists

    $existing_student = getRecord('users', "name = '$student_name' AND id != $student_id");

    if ($existing_student) {
        header('location:../views/admin/admin-students.php?msg=Student name already exists');
        exit();
    }

    $data = [
        'name' => $student_name,
        'email' => $student_email,
        'gender' => $student_gender

    ];

    if (editRecord('users', $data, "id = $student_id")) {
        header('location:../views/admin/admin-students.php?msg=Student updated successfully');
    }
}
