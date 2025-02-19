<?php
include '../models/functions.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $classroom = getRecord('classrooms', "id = $id");

    if ($classroom) {
        echo json_encode($classroom);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Classroom not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}

if (isset($_POST['submit'])) {
    $room_number = $_POST['room_number'];
    $type = $_POST['type'];
    $capacity = $_POST['capacity'];
    $classroom_id = $_GET['id'];

    // Check if classroom name already exists

    $existing_classroom = getRecord('classrooms', "room_number = '$room_number' AND id != $classroom_id");

    if ($existing_classroom) {
        header('location:../views/admin/admin-classrooms.php?msg=Classroom name already exists');
        exit();
    }

    $data = [
        'room_number' => $room_number,
        'type' => $type,
        'capacity' => $capacity
    ];

    if (editRecord('classrooms', $data, "id = $classroom_id")) {
        header('location:../views/admin/admin-classrooms.php?msg=Classroom updated successfully');
    }
}
