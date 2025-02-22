<?php
include '../models/functions.php';


header('Content-Type: application/json');

$total_students = countAllRecords('users', 'WHERE user_type = "student"');
$total_teachers = countAllRecords('users', 'WHERE user_type = "teacher"');
$total_subjects = countAllRecords('subjects');
$total_sections = countAllRecords('sections');
$total_enrollments = countAllRecords('enrollments');
$total_classrooms = countAllRecords('classrooms');

$data = [
    'total_students' => $total_students,
    'total_teachers' => $total_teachers,
    'total_subjects' => $total_subjects,
    'total_sections' => $total_sections,
    'total_enrollments' => $total_enrollments,
    'total_classrooms' => $total_classrooms
];

echo json_encode($data);
