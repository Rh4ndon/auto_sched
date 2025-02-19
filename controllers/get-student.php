<?php
include '../models/functions.php';

if (isset($_GET['id'])) {
    $GET_ID = $_GET['id'];

    // Assuming you have a function to get the student name by ID
    $student = getRecord('users', 'id =' . $GET_ID);

    $studentName = $student['name'] ?? '';

    header('Content-Type: application/json');
    if ($studentName) {
        echo json_encode(['status' => 'success', 'name' => $studentName]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Student not found.']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'ID not provided.']);
}
