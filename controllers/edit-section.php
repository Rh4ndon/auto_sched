<?php
include '../models/functions.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $section = getRecord('sections', "id = $id");

    if ($section) {
        echo json_encode($section);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Section not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}

if (isset($_POST['submit'])) {
    $department_code = $_POST['department_code'];
    $section_code = $_POST['section_code'];
    $semester = $_POST['semester'];
    $year_level = $_POST['year_level'];
    $academic_year = $_POST['academic_year'];
    $section_id = $_GET['id'];

    $section_name = $department_code . '-' . $section_code;

    // Check if section name already exists

    $existing_section = getRecord('sections', "section_name = '$section_name' AND id != $section_id");

    if ($existing_section) {
        header('location:../views/admin/admin-sections.php?msg=Section name already exists');
        exit();
    }

    $data = [
        'section_name' => $section_name,
        'semester' => $semester,
        'year_level' => $year_level,
        'academic_year' => $academic_year
    ];

    if (editRecord('sections', $data, "id = $section_id")) {
        header('location:../views/admin/admin-sections.php?msg=Section updated successfully');
    }
}
