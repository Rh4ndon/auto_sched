<?php
@include '../models/functions.php';
// Debugger
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['submit'])) {
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $department = $_POST['department'];
    $semester = $_POST['semester'];
    $year_level = $_POST['year_level'];
    $subject_type = $_POST['subject_type'];
    $minutes_per_week = $_POST['minutes_per_week'];
    $units = $_POST['units'];

    // Check if subject code, subject name already exists
    $existing_subject = getRecord('subjects', "subject_code = '$subject_code'");

    //$existing_subject_name = getRecord('subjects', "subject_name = '$subject_name'");

    if ($existing_subject || $existing_subject_name) {
        // Redirect to the subjects page with an error message
        header('location:../views/admin/admin-subjects.php?msg=Subject code already exists');
        exit();
    }


    $data = [
        'subject_code' => $subject_code,
        'subject_name' => $subject_name,
        'semester' => $semester,
        'year_level' => $year_level,
        'subject_type' => $subject_type,
        'minutes_per_week' => $minutes_per_week,
        'units' => $units,
        'department' => $department
    ];

    if (insertRecord('subjects', $data)) {
        header('location:../views/admin/admin-subjects.php?msg=Subject added successfully');
    }
}
