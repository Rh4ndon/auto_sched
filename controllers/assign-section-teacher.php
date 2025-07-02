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
    $sections = $data['sections'];
    $year = $data['academic_year'];

    foreach ($sections as $section) {
        $assignData = [
            'teacher_id' => $id,
            'section_id' => $section,
            'semester' => $semester,
            'academic_year' => $year,

        ];

        $assignSuccess = insertRecord('teacher_sections', $assignData);

        if (!$assignSuccess) {
            throw new Exception('Failed to assign teacher in section ID: ' . $section);
        }
    }

    $sectionDetails = [];
    foreach ($sections as $section) {
        $sectionDetails[] = getRecord('sections', "id = '$section'");
    }
    echo json_encode(['success' => true, 'sections' => $sectionDetails]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
