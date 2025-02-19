<?php
@include '../models/functions.php';
// Debugger
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['submit'])) {
    $room_number = $_POST['room_number'];
    $capacity = $_POST['capacity'];
    $type = $_POST['type'];

    // Check if classroom name already exists
    $existing_classroom = getRecord('classrooms', "room_number = '$room_number'");

    if ($existing_classroom) {
        header('location:../views/admin/admin-classrooms.php?msg=Classroom already exists');
        exit();
    }

    $data = [
        'room_number' => $room_number,
        'capacity' => $capacity,
        'type' => $type
    ];

    if (insertRecord('classrooms', $data)) {
        header('location:../views/admin/admin-classrooms.php?msg=Classroom added successfully');
    } else {
        header('location:../views/admin/admin-classrooms.php?msg=Failed to add classroom');
    }
    exit();
}
