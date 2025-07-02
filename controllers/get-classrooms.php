<?php
include '../models/functions.php';
$classrooms = getAllRecords('classrooms');

$response = array();
if (!empty($classrooms)) {
    $count = 1;
    foreach ($classrooms as $row) {

        $response[] = array(
            'count' => $count++,
            'room_number' => $row['room_number'],
            'room_name' => $row['room_name'],
            'capacity' => $row['capacity'],
            'type' => $row['type'],
            'id' => $row['id'],
            'department' => $row['department'] ? $row['department'] : 'N/A' // Handle empty department
        );
    }
} else {
    $response['error'] = 'No classrooms found';
}

header('Content-Type: application/json');
echo json_encode($response);
