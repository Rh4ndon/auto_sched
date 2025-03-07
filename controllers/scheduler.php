<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../models/functions.php';

// Debug: Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
echo "Database connection successful!";
// Function to fetch subjects based on type, semester, and academic year
function getSubjects($conn, $subjectType, $semester, $academicYear)
{
    $stmt = $conn->prepare("
        SELECT subjects.*, teacher_subjects.teacher_id, COUNT(enrollments.id) AS students_count, enrollments.section_id 
        FROM teacher_subjects 
        INNER JOIN subjects ON teacher_subjects.subject_id = subjects.id 
        INNER JOIN enrollments ON subjects.id = enrollments.subject_id 
        WHERE subjects.subject_type = ? 
        AND subjects.semester = ? 
        AND enrollments.academic_year = ?
        GROUP BY subjects.id, enrollments.section_id
    ");

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param('sss', $subjectType, $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to fetch classrooms based on type
function getClassrooms($conn, $type)
{
    $condition = $type === 'lab' ? "Laboratory" : ($type === 'pe' ? "Gym" : "Room");
    $stmt = $conn->prepare("SELECT id, room_number, capacity FROM classrooms WHERE type = ?");
    $stmt->bind_param('s', $condition);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to fetch all sections for the given semester and academic year
function getSections($conn, $semester, $academicYear)
{
    $stmt = $conn->prepare("SELECT * FROM sections WHERE semester = ? AND academic_year = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param('ss', $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Function to check for scheduling conflicts
function checkConflict($conn, $day, $examType, $startTime, $endTime, $teacherId, $classroomId, $sectionId, $semester, $academicYear)
{
    $stmt = $conn->prepare("
        SELECT 1 FROM schedules 
        WHERE day = ? 
        AND exam_type = ?
        AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?)) 
        AND (teacher_id = ? OR classroom_id = ? OR section_id = ?) 
        AND semester = ? 
        AND academic_year = ?
        LIMIT 1
    ");

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return true;
    }

    $stmt->bind_param('sssssiiisss', $day, $examType, $endTime, $startTime, $startTime, $endTime, $teacherId, $classroomId, $sectionId, $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Function to check if a subject is already scheduled for a section on a specific day
function isSubjectScheduled($conn, $subjectId, $sectionId, $day, $semester, $academicYear)
{
    $stmt = $conn->prepare("
        SELECT 1 FROM schedules 
        WHERE subject_id = ? 
        AND section_id = ? 
        AND day = ? 
        AND semester = ? 
        AND academic_year = ?
        LIMIT 1
    ");

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return true;
    }

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
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param('iiisssssssi', $subjectId, $teacherId, $classroomId, $day, $startTime, $endTime, $semester, $academicYear, $examType, $subjectType, $sectionId);

    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }

    return true;
}


/**
 * Calculate how many slots are needed based on subject type and weekly minutes.
 */
function calculateNeededSlots($type, $minutesPerWeek)
{
    if ($type === 'lecture') {
        return ceil($minutesPerWeek / 60); // 1hr per lecture
    } elseif ($type === 'lab') {
        return ceil($minutesPerWeek / 90); // 1.5hr per lab
    } elseif ($type === 'pe') {
        return 1; // Once per week
    }
    return 0;
}

/**
 * Find a suitable classroom for the subject.
 */
function findSuitableClassroom($conn, $subjectType, $studentsCount, $classrooms)
{
    foreach ($classrooms as $classroom) {
        if ($classroom['capacity'] >= $studentsCount) {
            return $classroom;
        }
    }
    return null;
}

/**
 * Allocate time slots for the subject.
 */
function allocateTimeSlots($conn, $schoolHours, $lunchBreak, $neededSlots, $subjectType, $teacherId, $classroomId, $sectionId, $semester, $academicYear, $examType)
{
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday'];
    $allocatedSlots = [];

    foreach ($days as $day) {
        $daySlots = generateDaySlots($schoolHours[$day], $lunchBreak, $neededSlots);

        foreach ($daySlots as $slot) {
            [$startTime, $endTime] = $slot;

            // Check for conflicts
            if (checkConflict($conn, $day, $examType, $startTime, $endTime, $teacherId, $classroomId, $sectionId, $semester, $academicYear)) {
                continue; // Conflict found, try next slot
            }

            // Allocate the slot
            $allocatedSlots[] = [
                'day' => $day,
                'start_time' => $startTime,
                'end_time' => $endTime
            ];

            $neededSlots--;
            if ($neededSlots <= 0) {
                return $allocatedSlots; // Done allocating slots
            }
        }
    }

    return $allocatedSlots;
}

/**
 * Generate available time slots for a given day.
 */
function generateDaySlots($dayHours, $lunchBreak, $neededSlots)
{
    $start = strtotime($dayHours[0]);
    $end = strtotime($dayHours[1]);
    $lunchStart = strtotime($lunchBreak[0]);
    $lunchEnd = strtotime($lunchBreak[1]);

    $slots = [];
    $current = $start;

    while ($current + 3600 <= $end) {
        $next = $current + 3600; // 60 mins slot
        if ($current >= $lunchStart && $next <= $lunchEnd) {
            $current = $lunchEnd; // Skip lunch break
            continue;
        }

        $slots[] = [date('H:i', $current), date('H:i', $next)];
        $current = $next + 3600; // 1hr common break after every class
    }

    return array_slice($slots, 0, $neededSlots); // Return only what's needed
}


// Hardcoded values for testing
$semester = '1'; // Hardcoded semester
$examType = 'none'; // Hardcoded exam type
$academicYear = '2024-2025'; // Hardcoded academic year

// Define school hours and breaks
$schoolHours = [
    'Monday' => ['08:00', '17:00'],
    'Tuesday' => ['08:00', '17:00'],
    'Wednesday' => ['08:00', '17:00'],
    'Thursday' => ['08:00', '17:00']
];
$lunchBreak = ['12:00', '13:00'];

// Fetch all sections for the semester and academic year
$sections = getSections($conn, $semester, $academicYear);

// Debug: Print sections
echo "<pre>Sections:\n";
print_r($sections);
echo "</pre>";

// Iterate through each section
foreach ($sections as $section) {
    $sectionId = $section['id'];

    // Fetch all subjects for the section
    $subjects = getSubjects($conn, null, $semester, $academicYear);

    // Debug: Print subjects
    echo "<pre>Subjects for Section {$section['section_name']}:\n";
    print_r($subjects->fetch_all(MYSQLI_ASSOC));
    echo "</pre>";

    // Iterate through each subject for the section
    while ($subject = $subjects->fetch_assoc()) {
        $subjectType = $subject['subject_type'];
        $minutesPerWeek = $subject['minutes_per_week'];
        $teacherId = $subject['teacher_id'];

        // Calculate the number of slots needed for the subject
        $neededSlots = calculateNeededSlots($subjectType, $minutesPerWeek);

        // Find a suitable classroom for the subject
        $classroom = findSuitableClassroom($conn, $subjectType, $subject['students_count'], $classrooms);

        if (!$classroom) {
            error_log("No suitable classroom found for subject: {$subject['subject_name']} in section: {$section['section_name']}");
            continue; // Skip this subject
        }

        // Allocate time slots for the subject
        $allocatedSlots = allocateTimeSlots($conn, $schoolHours, $lunchBreak, $neededSlots, $subjectType, $teacherId, $classroom['id'], $sectionId, $semester, $academicYear, $examType);

        if (empty($allocatedSlots)) {
            error_log("No valid slots found for subject: {$subject['subject_name']} in section: {$section['section_name']}");
            continue; // Skip this subject
        }

        // Save the allocated slots to the database
        foreach ($allocatedSlots as $slot) {
            echo "<pre>Assigning Schedule for {$subject['subject_name']}:\n";
            print_r([
                'subject_id' => $subject['id'],
                'teacher_id' => $teacherId,
                'classroom_id' => $classroom['id'],
                'day' => $slot['day'],
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'semester' => $semester,
                'academic_year' => $academicYear,
                'exam_type' => $examType,
                'subject_type' => $subjectType,
                'section_id' => $sectionId
            ]);
            echo "</pre>";

            assignSchedule($conn, $subject['id'], $teacherId, $classroom['id'], $slot['day'], $slot['start_time'], $slot['end_time'], $semester, $academicYear, $examType, $subjectType, $sectionId);
        }
    }
}

$conn->close();
//header("Location: ../views/admin/admin-scheduler.php?msg=Schedule generated successfully");
echo "Schedule generated successfully";
exit();
