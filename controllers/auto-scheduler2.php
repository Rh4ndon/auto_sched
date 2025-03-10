<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../models/functions.php';

// Function to fetch subjects based on type, semester, and academic year
function getSubjects($conn, $subjectType, $semester, $academicYear)
{
    $stmt = $conn->prepare("
        SELECT subjects.*, teacher_subjects.teacher_id, COUNT(enrollments.id) AS students_count, enrollments.section_id 
        FROM teacher_subjects 
        INNER JOIN subjects ON teacher_subjects.subject_id = subjects.id 
        INNER JOIN enrollments ON subjects.id = enrollments.subject_id 
        WHERE subjects.subject_type = ? AND subjects.semester = ? AND enrollments.academic_year = ?
        GROUP BY subjects.id, enrollments.section_id
    ");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('sss', $subjectType, $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to fetch classrooms based on type
function getClassrooms($conn, $type)
{
    $condition = $type === 'lab' ? "Laboratory" : "Room";
    $stmt = $conn->prepare("SELECT id, room_number, capacity FROM classrooms WHERE type = ?");
    $stmt->bind_param('s', $condition);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to check for scheduling conflicts
function checkConflict($conn, $day, $exam_type, $startTime, $endTime, $teacherId, $classroomId, $sectionId, $semester, $academicYear)
{
    $stmt = $conn->prepare("
        SELECT * FROM schedules 
        WHERE day = ? 
        AND exam_type = ?
        AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?)) 
        AND (teacher_id = ? OR classroom_id = ? OR section_id = ?) 
        AND semester = ? 
        AND academic_year = ?
    ");
    $stmt->bind_param('sssssiiisss', $day, $exam_type, $endTime, $startTime, $startTime, $endTime, $teacherId, $classroomId, $sectionId, $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Function to check if a subject is already scheduled for a section on a specific day
function isSubjectScheduled($conn, $subjectId, $sectionId, $day, $semester, $academicYear)
{
    $stmt = $conn->prepare("
        SELECT * FROM schedules 
        WHERE subject_id = ? 
        AND section_id = ? 
        AND day = ? 
        AND semester = ? 
        AND academic_year = ?
    ");
    $stmt->bind_param('iisss', $subjectId, $sectionId, $day, $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Function to assign a schedule
function assignSchedule($conn, $subjectId, $teacherId, $classroomId, $day, $startTime, $endTime, $semester, $academicYear, $examType, $subjectType, $sectionId)
{
    $stmt = $conn->prepare("
        INSERT INTO schedules (subject_id, teacher_id, classroom_id, day, start_time, end_time, semester, academic_year, exam_type, subject_type, section_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('iiisssssssi', $subjectId, $teacherId, $classroomId, $day, $startTime, $endTime, $semester, $academicYear, $examType, $subjectType, $sectionId);

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    return true;
}

if (isset($_POST['submit'])) {
    $semester = $_POST['semester']; // Ensure this matches the `semester` values in the database (e.g., '1', '2', 'midyear')
    $examType = $_POST['type']; // This is for exam_type (none, prelim, midterms, finals)
    $academicYear = $_POST['academic_year']; // Use the academic year from the form (e.g., '2024-2025')

    $days = [
        'lecture' => ['Monday', 'Wednesday', 'Friday'],
        'lab' => ['Tuesday', 'Thursday']
    ];

    $timeSlots = [
        'lecture' => [
            ['07:00:00', '08:00:00'],
            ['08:00:00', '09:00:00'],
            ['09:00:00', '10:00:00'],
            ['10:00:00', '11:00:00'],
            ['11:00:00', '12:00:00'],
            ['13:00:00', '14:00:00'],
            ['14:00:00', '15:00:00'],
            ['15:00:00', '16:00:00']
        ],
        'lab' => [
            ['07:00:00', '08:30:00'],
            ['08:30:00', '10:00:00'],
            ['10:00:00', '11:30:00'],
            ['13:00:00', '14:30:00'],
            ['14:30:00', '16:00:00']
        ]
    ];

    foreach (['lecture', 'lab'] as $subjectType) {
        $subjects = getSubjects($conn, $subjectType, $semester, $academicYear);
        $classrooms = getClassrooms($conn, $subjectType);

        while ($subject = $subjects->fetch_assoc()) {
            $classrooms->data_seek(0); // Reset pointer for classrooms
            while ($classroom = $classrooms->fetch_assoc()) {
                if ($subject['students_count'] <= $classroom['capacity']) {
                    foreach ($days[$subjectType] as $day) {
                        // Check if the subject is already scheduled for this section on this day
                        if (!isSubjectScheduled($conn, $subject['id'], $subject['section_id'], $day, $semester, $academicYear)) {
                            foreach ($timeSlots[$subjectType] as $slot) {
                                $conflict = checkConflict($conn, $day, $examType, $slot[0], $slot[1], $subject['teacher_id'], $classroom['id'], $subject['section_id'], $semester, $academicYear);
                                if (!$conflict) {
                                    assignSchedule($conn, $subject['id'], $subject['teacher_id'], $classroom['id'], $day, $slot[0], $slot[1], $semester, $academicYear, $examType, $subjectType, $subject['section_id']);
                                    continue 2; // Move to the next subject after scheduling
                                } else {
                                }
                            }
                        } else {
                        }
                    }
                } else {
                }
            }
        }
    }

    $conn->close();
    header("Location: ../views/admin/admin-scheduler.php?msg=Schedule generated successfully");
    exit();
}
