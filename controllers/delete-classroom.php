<?php
include '../models/functions.php';
// Method DELETE
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $classroom_id = $_GET['id'];
    $condition = "id = $classroom_id";
    $response = array();
    if (deleteRecord('classrooms', $condition)) {
        $response['success'] = true;
        $response['message'] = 'Classroom deleted successfully';
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to delete classroom';
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}
