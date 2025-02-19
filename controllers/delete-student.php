<?php
include '../models/functions.php';
// Method DELETE
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $student_id = $_GET['id'];
    $condition = "id = $student_id";
    $response = array();
    // First, delete the related enrollments
    $enrollment_condition = "student_id = $student_id";
    if (deleteRecord('enrollments', $enrollment_condition)) {
        // Then, delete the student record
        if (deleteRecord('users', $condition)) {
            $response['success'] = true;
            $response['message'] = 'Student and related enrollments deleted successfully';
        } else {
            $response['success'] = false;
            $response['message'] = 'Failed to delete student';
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to delete related enrollments';
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}
