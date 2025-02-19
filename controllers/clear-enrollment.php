<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../models/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $condition = "student_id = $id";

        if (deleteRecord('enrollments', $condition)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete record']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID not provided']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
