<?php
@include '../models/functions.php';
// Debugger
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['submit'])) {
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $semester = $_POST['semester'];
    $year_level = $_POST['year_level'];
    $subject_type = $_POST['subject_type'];

    $data = [
        'subject_code' => $subject_code,
        'subject_name' => $subject_name,
        'semester' => $semester,
        'year_level' => $year_level,
        'subject_type' => $subject_type
    ];

    if (insertRecord('subjects', $data)) {
        header('location:../views/admin/admin-subjects.php?msg=Subject added successfully');
    }
}
