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
    $academic_year = $data['academic_year'];

    // Check for units enrolled
    $units_enrolled = 0;
    foreach ($subjects as $subject) {
        $subject_record = getRecord('subjects', "id = '$subject'");
        $units_enrolled += $subject_record['units'];
    }

    if ($units_enrolled > 26) {
        throw new Exception('Student cannot enroll in more than 26 units');
    }

    // Check if the student is already enrolled in any section
    $enrollments = getRecord('enrollments', "student_id = '$id'");
    if (empty($enrollments)) {
        $sectionRecord = getRecord('sections', "id = '$section'");
        $newStudentCount = $sectionRecord['student_count'] + 1;
        editRecord('sections', ['student_count' => $newStudentCount], "id = '$section'");
    }

    foreach ($subjects as $subject) {
        $subjectUnits = getRecord('subjects', "id = '$subject'");
        $enrollData = [
            'student_id' => $id,
            'subject_id' => $subject,
            'section_id' => $section,
            'semester' => $semester,
            'academic_year' => $academic_year,
            'year_level' => $year,
            'units' => $subjectUnits['units'],
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
