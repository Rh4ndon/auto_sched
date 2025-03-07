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
        if ($row['subject_type'] == 'lecture') {
            $subject_type = 'Lecture';
        } elseif ($row['subject_type'] == 'pe') {
            $subject_type = 'PE';
        } else {
            $subject_type = 'Lab';
        }
        $response[] = array(
            'count' => $count++,
            'subject_code' => $row['subject_code'],
            'subject_name' => $row['subject_name'],
            'semester' => $semester,
            'year_level' => $row['year_level'],
            'subject_type' => $subject_type,
            'minutes_per_week' => $row['minutes_per_week'],
            'units' => $row['units'],
            'id' => $row['id']
        );
    }
} else {
    $response['error'] = 'No subjects found';
}

header('Content-Type: application/json');
echo json_encode($response);
