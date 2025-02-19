<?php
include '../models/functions.php';
// Method DELETE
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $section_id = $_GET['id'];
    $condition = "id = $section_id";
    $response = array();
    if (deleteRecord('sections', $condition)) {
        $response['success'] = true;
        $response['message'] = 'Section deleted successfully';
    } else {
        $response['success'] = false;
        $response['message'] = 'Failed to delete section';
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}
