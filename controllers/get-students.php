<?php
// Debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../models/functions.php';
$students = getAllRecords('users', 'WHERE user_type = "student" ORDER BY gender ASC , name ASC');

$response = array();
if (!empty($students)) {
    $count = 1;
    foreach ($students as $student) {

        // Fetch all enrollments for the student, ordered by academic_year and semester
        $enrollments = getAllRecords(
            'enrollments',
            'WHERE student_id = ' . $student['id'] . ' ORDER BY enrollments.academic_year DESC, enrollments.semester DESC, enrollments.id DESC'
        );


        // Debugging: Print the enrollments for this student
        $debug = array();
        $debug['enrollments'] = $enrollments;

        $subjects = array();
        $semester = 'Not Yet Enrolled';
        $year_level = 'Not Yet Enrolled';
        $section_name = 'Not Yet Enrolled';

        if (!empty($enrollments)) {
            // Collect all subjects
            foreach ($enrollments as $enrollment) {

                if ($enrollment['semester'] == 1) {
                    $semester = '1st Semester';
                } elseif ($enrollment['semester'] == 2) {
                    $semester = '2nd Semester';
                } elseif ($enrollment['semester'] == 'midyear') {
                    $semester = 'Midyear';
                }

                if ($enrollment['year_level'] == 1) {
                    $year_level = '1st Year';
                } elseif ($enrollment['year_level'] == 2) {
                    $year_level = '2nd Year';
                } elseif ($enrollment['year_level'] == 3) {
                    $year_level = '3rd Year';
                } elseif ($enrollment['year_level'] == 4) {
                    $year_level = '4th Year';
                }

                $get_section = getRecord('sections', 'id = ' . $enrollment['section_id']);
                $section_name = $get_section['section_name'];

                $subject = getRecord('subjects', 'id = ' . $enrollment['subject_id']);
                $subjects[] = array(
                    'subject_name' => $subject['subject_name'],
                    'subject_code' => $subject['subject_code']
                );
            }
        } else {
            $subjects[] = array(
                'subject_name' => 'Not Yet Enrolled',
                'subject_code' => 'Not Yet Enrolled'
            );
        }
        $debug = array(
            'enrollments' => $enrollments,
            'raw_enrollments' => json_encode($enrollments) // Extra logging for debugging
        );

        $response[] = array(
            'count' => $count++,
            'name' => $student['name'],
            'email' => $student['email'],
            'gender' => $student['gender'],
            'section' => $section_name,
            'semester' => $semester,
            'year_level' => $year_level,
            'subjects' => $subjects,
            'id' => $student['id'],
            'debug' => $debug
        );
    }
} else {
    $response['error'] = 'No students found';
}

header('Content-Type: application/json');
echo json_encode($response);
