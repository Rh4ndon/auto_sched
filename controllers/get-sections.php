<?php
include '../models/functions.php';
$sections = getAllRecords('sections');

$response = array();
if (!empty($sections)) {
    $count = 1;
    foreach ($sections as $row) {
        if ($row['semester'] == '1') {
            $semester = '1st Semester';
        } else {
            $semester = '2nd Semester';
        }
        $response[] = array(
            'count' => $count++,
            'section_name' => $row['section_name'],
            'semester' => $semester,
            'year_level' => $row['year_level'],
            'id' => $row['id'],
            'academic_year' => $row['academic_year']
        );
    }
} else {
    $response['error'] = 'No sections found';
}

header('Content-Type: application/json');
echo json_encode($response);
