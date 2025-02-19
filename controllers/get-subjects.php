<?php
include '../models/functions.php';
$subjects = getAllRecords('subjects');

$response = array();
if (!empty($subjects)) {
    $count = 1;
    foreach ($subjects as $row) {
        if ($row['semester'] == '1') {
            $semester = '1st Semester';
        } else {
            $semester = '2nd Semester';
        }
        $response[] = array(
            'count' => $count++,
            'subject_code' => $row['subject_code'],
            'subject_name' => $row['subject_name'],
            'semester' => $semester,
            'year_level' => $row['year_level'],
            'id' => $row['id']
        );
    }
} else {
    $response['error'] = 'No subjects found';
}

header('Content-Type: application/json');
echo json_encode($response);
