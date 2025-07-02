<?php
// Debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../models/functions.php';
$teachers = getAllRecords('users', 'WHERE user_type = "teacher" ORDER BY gender ASC , name ASC');

$response = array();
if (!empty($teachers)) {
    $count = 1;
    foreach ($teachers as $teacher) {

        // Fetch all teacher_subjects for the teacher, ordered by academic_year and semester
        $teacher_subjects = getAllRecords(
            'teacher_subjects',
            'WHERE teacher_id = ' . $teacher['id'] . ' ORDER BY teacher_subjects.semester DESC'
        );
        // Fetch all teacher_sections for the teacher, ordered by academic_year and semester
        $teacher_sections = getAllRecords(
            'teacher_sections',
            'WHERE teacher_id = ' . $teacher['id'] . ' ORDER BY teacher_sections.semester DESC'
        );

        // Debugging: Print the teacher_subjects for this teacher
        $debug = array();
        $debug['teacher_subjects'] = $teacher_subjects;
        $debug['teacher_sections'] = $teacher_sections;

        $subjects = array();
        $sections = array();
        $semester = 'Not Yet Assigned';

        if (!empty($teacher_subjects)) {
            // Collect all subjects
            foreach ($teacher_subjects as $teacher_subject) {

                if ($teacher_subject['semester'] == 1) {
                    $semester = '1st Semester';
                } elseif ($teacher_subject['semester'] == 2) {
                    $semester = '2nd Semester';
                } elseif ($teacher_subject['semester'] == 'midyear') {
                    $semester = 'Midyear';
                }

                $subject = getRecord('subjects', 'id = ' . $teacher_subject['subject_id']);
                $subjects[] = array(
                    'subject_name' => $subject['subject_name'],
                    'subject_code' => $subject['subject_code']
                );
            }
        } else {
            $subjects[] = array(
                'subject_name' => 'Not Yet Assigned',
                'subject_code' => 'Not Yet Assigned'
            );
        }

        if (!empty($teacher_sections)) {
            // Collect all sections
            foreach ($teacher_sections as $teacher_section) {
                $section = getRecord('sections', 'id = ' . $teacher_section['section_id']);
                $sections[] = array(
                    'section_name' => $section['section_name'],
                    'year_level' => $section['year_level'],
                );
            }
        } else {
            $sections[] = array(
                'section_name' => 'Not Yet Assigned',
                'year_level' => 'Not Yet Assigned'
            );
        }

        $debug = array(
            'teacher_subjects' => $teacher_subjects,
            'teacher_sections' => $teacher_sections,
            'raw_teacher_subjects' => json_encode($teacher_subjects),
            'raw_teacher_sections' => json_encode($teacher_sections)
        );

        $response[] = array(
            'count' => $count++,
            'name' => $teacher['name'],
            'email' => $teacher['email'],
            'gender' => $teacher['gender'],
            'semester' => $semester,
            'subjects' => $subjects,
            'sections' => $sections,
            'id' => $teacher['id'],
            'debug' => $debug
        );
    }
} else {
    $response['error'] = 'No teachers found';
}

header('Content-Type: application/json');
echo json_encode($response);
