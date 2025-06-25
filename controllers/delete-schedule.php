<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../models/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Parse the input data
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data === null) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    $semester = $data['semester'];
    $type = $data['type'];
    $academic_year = $data['academic_year'];

    // Construct the condition
    $condition = "semester = '$semester' AND exam_type = '$type' AND academic_year = '$academic_year'";

    // Check if the record exists
    $existingRecord = getRecord('schedules', $condition);

    if (!$existingRecord) {
        echo json_encode(['success' => false, 'message' => 'Schedule not found']);
        exit;
    }

    // Delete the record
    $result = deleteRecord('schedules', $condition);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete schedule']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
