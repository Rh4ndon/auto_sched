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
        // First, create a mapping of ENUM values to display names
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
            'BTVTED_All' => 'BTVTED (All Specializations)',
            'BSED_All' => 'BSED (All Specializations)'
        ];

        // Then in your response array
        $response[] = array(
            'count' => $count++,
            'subject_code' => $row['subject_code'],
            'subject_name' => $row['subject_name'],
            'semester' => $semester,
            'year_level' => $row['year_level'],
            'subject_type' => $subject_type,
            'minutes_per_week' => $row['minutes_per_week'],
            'units' => $row['units'],
            'id' => $row['id'],
            'department' => $departmentDisplayNames[$row['department']] ?? 'N/A' // Friendly display name
        );
    }
} else {
    $response['error'] = 'No subjects found';
}

header('Content-Type: application/json');
echo json_encode($response);
