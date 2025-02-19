<?php
include '../models/functions.php';
// Method DELETE
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $subject_id = $_GET['id'];
    $condition = "id = $subject_id";
    $response = array();
    if (deleteRecord('subjects', $condition)) {
        $response['success'] = true;
        $response['message'] = 'Subject deleted successfully';
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to delete subject';
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}
