<?php
@include '../models/functions.php';
// Debugger
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['submit'])) {
    $department_code = $_POST['department_code'];
    $section_code = $_POST['section_code'];
    $semester = $_POST['semester'];
    $year_level = $_POST['year_level'];
    $academic_year = $_POST['academic_year'];

    $section_name = $department_code . '-' . $section_code;

    // Check if section name already exists
    $existing_section = getRecord('sections', "section_name = '$section_name'");

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

    if (insertRecord('sections', $data)) {
        header('location:../views/admin/admin-sections.php?msg=Section added successfully');
    } else {
        header('location:../views/admin/admin-sections.php?msg=Failed to add section');
    }
    exit();
}
