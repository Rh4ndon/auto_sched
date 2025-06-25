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
        $departmentDisplayNames = [
            'BSIT' => 'BSIT',
            'BSED_Mathematics' => 'BSED (Major in Mathematics)',
            'BSED_Social_Studies' => 'BSED (Major in Social Studies)',
            'BTVTED_Garments' => 'BTVTED (Garments, Fashion Design)',
            'BTVTED_Electronics' => 'BTVTED (Electronics Technology)',
            'BTVTED_Electrical' => 'BTVTED (Electrical Technology)',
            'Diploma_Agricultural_Sciences' => 'Diploma in Agricultural Sciences',
            'BAT_Crops_Production' => 'BAT (Major in Crops Production)',
            'BSA_Agronomy' => 'BSA (Major in Agronomy)',
            'General_Education' => 'General Education',
            'BTVTED_All' => 'BSTVTED',
            'BSED_All' => 'BSED'
        ];
        $response[] = array(
            'count' => $count++,
            'section_name' => $row['section_name'],
            'semester' => $semester,
            'year_level' => $row['year_level'],
            'id' => $row['id'],
            'academic_year' => $row['academic_year'],
            'student_count' => $row['student_count'],
            'department' => $departmentDisplayNames[$row['department']] ?? 'N/A'
        );
    }
} else {
    $response['error'] = 'No sections found';
}

header('Content-Type: application/json');
echo json_encode($response);
