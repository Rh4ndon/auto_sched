<?php
include '../models/functions.php';
// Method DELETE
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $teacher_id = $_GET['id'];
    $condition = "id = $teacher_id";
    $response = array();
    // First, delete the related teacher_subjects
    $teacher_subject_condition = "teacher_id = $teacher_id";
    if (deleteRecord('teacher_subjects', $teacher_subject_condition)) {
        // Then, delete the teacher record
        if (deleteRecord('users', $condition)) {
            $response['success'] = true;
            $response['message'] = 'Teacher and related teacher_subjects deleted successfully';
        } else {
            $response['success'] = false;
            $response['message'] = 'Failed to delete teacher';
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to delete related teacher_subjects';
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}
