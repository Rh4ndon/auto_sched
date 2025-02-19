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
    $section = $data['section'];
    $semester = $data['semester'];
    $year = $data['year'];
    $subjects = $data['subjects'];

    foreach ($subjects as $subject) {
        $enrollData = [
            'student_id' => $id,
            'subject_id' => $subject,
            'section_id' => $section,
            'semester' => $semester,
            'academic_year' => $year,
            'year_level' => $year,
        ];

        $enrollSuccess = insertRecord('enrollments', $enrollData);

        if (!$enrollSuccess) {
            throw new Exception('Failed to enroll student in subject ID: ' . $subject);
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
