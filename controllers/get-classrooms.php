<?php
include '../models/functions.php';
$classrooms = getAllRecords('classrooms');

$response = array();
if (!empty($classrooms)) {
    $count = 1;
    foreach ($classrooms as $row) {
        if ($row['semester'] == '1') {
            $semester = '1st Semester';
        } else {
            $semester = '2nd Semester';
        }
        $response[] = array(
            'count' => $count++,
            'room_number' => $row['room_number'],
            'capacity' => $row['capacity'],
            'type' => $row['type'],
            'id' => $row['id']
        );
    }
} else {
    $response['error'] = 'No classrooms found';
}

header('Content-Type: application/json');
echo json_encode($response);
