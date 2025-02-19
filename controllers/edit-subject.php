<?php
include '../models/functions.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $subject = getRecord('subjects', "id = $id");

    if ($subject) {
        echo json_encode($subject);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Subject not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}

if (isset($_POST['submit'])) {
    $subject_name = $_POST['subject_name'];
    $subject_code = $_POST['subject_code'];
    $subject_id = $_GET['id'];
    $semester = $_POST['semester'];
    $year_level = $_POST['year_level'];

    $data = [
        'subject_name' => $subject_name,
        'subject_code' => $subject_code,
        'semester' => $semester,
        'year_level' => $year_level
    ];

    if (editRecord('subjects', $data, "id = $subject_id")) {
        header('location:../views/admin/admin-subjects.php?msg=Subject updated successfully');
    }
}
