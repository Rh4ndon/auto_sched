<?php
include '../models/functions.php';

if (isset($_GET['id'])) {
    $GET_ID = $_GET['id'];

    // Assuming you have a function to get the teacher name by ID
    $teacher = getRecord('users', 'id =' . $GET_ID);

    $teacherName = $teacher['name'] ?? '';

    header('Content-Type: application/json');
    if ($teacherName) {
        echo json_encode(['status' => 'success', 'name' => $teacherName]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Teacher not found.']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'ID not provided.']);
}
