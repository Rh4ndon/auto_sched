<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../models/functions.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Invalid JSON input');
    }

    $id = $data['id'];
    $semester = $data['semester'];
    $subjects = $data['subjects'];
    $year = $data['academic_year'];

    foreach ($subjects as $subject) {
        $assignData = [
            'teacher_id' => $id,
            'subject_id' => $subject,
            'semester' => $semester,
            'academic_year' => $year,

        ];

        $assignSuccess = insertRecord('teacher_subjects', $assignData);

        if (!$assignSuccess) {
            throw new Exception('Failed to assign teacher in subject ID: ' . $subject);
        }
    }

    $subjectDetails = [];
    foreach ($subjects as $subject) {
        $subjectDetails[] = getRecord('subjects', "id = '$subject'");
    }
    echo json_encode(['success' => true, 'subjects' => $subjectDetails]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
