<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../models/functions.php';
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$debug = true;
function debugPrint($title, $data)
{
    global $debug;
    if ($debug) {
        echo "<pre>$title:\n";
        print_r($data);
        echo "</pre>";
    }
}

// === CONFIGURATION ===
$schoolHours = [
    ['07:00 AM', '08:00 AM'],
    ['07:00 AM', '08:30 AM'],
    ['09:00 AM', '10:00 AM'],
    ['09:00 AM', '10:30 AM'],
    ['11:00 AM', '12:00 PM'],
    ['01:00 PM', '02:00 PM'],
    ['01:00 PM', '01:30 PM'],
    ['03:00 PM', '04:00 PM'],
    ['03:00 PM', '04:30 PM'],
    ['04:00 PM', '05:00 PM'],
    ['04:00 PM', '05:30 PM']
];
$lunchBreak = ['12:00 PM', '01:00 PM'];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday'];

// === HELPERS ===

function getSubjectsForSection($conn, $sectionId, $semester, $academicYear)
{
    $sql = "
        SELECT subjects.*, teacher_subjects.teacher_id
        FROM subjects
        INNER JOIN teacher_subjects ON subjects.id = teacher_subjects.subject_id
        INNER JOIN enrollments ON subjects.id = enrollments.subject_id
        WHERE enrollments.section_id = ?
        AND subjects.semester = ?
        AND enrollments.academic_year = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $sectionId, $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getSections($conn, $semester, $academicYear)
{
    $stmt = $conn->prepare("SELECT * FROM sections WHERE semester = ? AND academic_year = ?");
    $stmt->bind_param('ss', $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getClassrooms($conn, $subjectType)
{
    $type = match ($subjectType) {
        'lab' => 'Laboratory',
        'pe' => 'Gym',
        default => 'Room'
    };
    $stmt = $conn->prepare("SELECT id, room_number, capacity FROM classrooms WHERE type = ?");
    $stmt->bind_param('s', $type);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function checkConflict($conn, $day, $start, $end, $teacherId, $roomId, $sectionId, $semester, $academicYear, $examType)
{
    $stmt = $conn->prepare("
        SELECT 1 FROM schedules 
        WHERE day = ? AND exam_type = ?
        AND (
            (start_time < ? AND end_time > ?) OR 
            (start_time < ? AND end_time > ?)
        )
        AND (teacher_id = ? OR classroom_id = ? OR section_id = ?)
        AND semester = ? AND academic_year = ?
    ");
    $stmt->bind_param('ssssssiiiss', $day, $examType, $end, $start, $start, $end, $teacherId, $roomId, $sectionId, $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function assignSchedule($conn, $subjectId, $teacherId, $roomId, $day, $start, $end, $semester, $academicYear, $examType, $subjectType, $sectionId)
{
    $stmt = $conn->prepare("
        INSERT INTO schedules 
        (subject_id, teacher_id, classroom_id, day, start_time, end_time, semester, academic_year, exam_type, subject_type, section_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iiisssssssi', $subjectId, $teacherId, $roomId, $day, $start, $end, $semester, $academicYear, $examType, $subjectType, $sectionId);
    if (!$stmt->execute()) {
        die("Failed to insert schedule: " . $stmt->error);
    }
}

function checkSubjectOnDay($conn, $subjectId, $day, $sectionId, $semester, $academicYear)
{
    $stmt = $conn->prepare("
        SELECT 1 FROM schedules 
        WHERE subject_id = ? AND day = ? AND section_id = ? AND semester = ? AND academic_year = ?
    ");
    $stmt->bind_param('issss', $subjectId, $day, $sectionId, $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getEnrolledStudents($conn, $semester, $academicYear)
{
    $stmt = $conn->prepare("SELECT * FROM enrollments WHERE semester = ? AND academic_year = ?");
    $stmt->bind_param('ss', $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getTeachers($conn, $semester, $academicYear)
{
    $stmt = $conn->prepare("SELECT * FROM teacher_subjects WHERE semester = ? AND academic_year = ?");
    $stmt->bind_param('ss', $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getExistingSchedules($conn, $semester, $academicYear)
{
    $stmt = $conn->prepare("SELECT * FROM schedules WHERE semester = ? AND academic_year = ?");
    $stmt->bind_param('ss', $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function generateDaySlots($schoolHours, $lunchBreak, $duration)
{
    $slots = [];
    foreach ($schoolHours as $block) {
        $startTime = strtotime($block[0]);
        $endTime = strtotime($block[1]);
        $lunchStartTime = strtotime($lunchBreak[0]);
        $lunchEndTime = strtotime($lunchBreak[1]);

        // Skip the block if it falls entirely within the lunch break
        if ($startTime >= $lunchStartTime && $endTime <= $lunchEndTime) {
            continue;
        }

        // Adjust the start time if it falls within the lunch break
        if ($startTime >= $lunchStartTime && $startTime <= $lunchEndTime) {
            $startTime = $lunchEndTime;
        }

        // Generate slots for the current block
        while (($startTime + ($duration * 60)) <= $endTime) {
            $nextTime = $startTime + ($duration * 60);
            $slots[] = [date('H:i', $startTime), date('H:i', $nextTime)];
            $startTime = $nextTime;
        }
    }
    return $slots;
}

// === MAIN SCHEDULE LOGIC ===
if (isset($_POST['submit'])) {
    $semester = $_POST['semester'];
    $examType = $_POST['type'];
    $academicYear = $_POST['academic_year'];

    // Check for existing schedules
    $existingSchedules = getExistingSchedules($conn, $semester, $academicYear);
    if (!empty($existingSchedules)) {
        header("Location: ../views/admin/admin-scheduler.php?error=Schedules already exist for the selected semester and academic year.");
        exit();
    }

    // Check for Enrolled students
    $enrolledStudents = getEnrolledStudents($conn, $semester, $academicYear);
    $teacherSubjects = getTeachers($conn, $semester, $academicYear);

    if (empty($enrolledStudents)) {
        header("Location: ../views/admin/admin-scheduler.php?error=No enrolled students found for the selected semester and academic year.");
        exit();
    }

    if (empty($teacherSubjects)) {
        header("Location: ../views/admin/admin-scheduler.php?error=No teachers found for the selected semester and academic year.");
        exit();
    }

    $sections = getSections($conn, $semester, $academicYear);

    if (empty($sections)) {
        header("Location: ../views/admin/admin-scheduler.php?error=No sections found.");
        exit();
    }

    // Pre-fetch all classrooms to reduce database queries
    $classrooms = [
        'lecture' => getClassrooms($conn, 'lecture'),
        'lab' => getClassrooms($conn, 'lab'),
        'pe' => getClassrooms($conn, 'pe')
    ];

    // === UPDATED SCHEDULING LOGIC ===
    foreach ($sections as $section) {
        $sectionId = $section['id'];
        $subjects = getSubjectsForSection($conn, $sectionId, $semester, $academicYear);

        $daySubjectHistory = []; // Track subjects assigned per day
        $classroomUsage = []; // Track classroom usage per day
        $subjectLoad = array_fill_keys($days, 0); // Track the number of subjects scheduled per day

        shuffle($subjects); // Randomize subjects   
        foreach ($subjects as $subject) {
            $rooms = $classrooms[$subject['subject_type']]; // Use pre-fetched classrooms

            if (empty($rooms)) {
                header("Location: ../views/admin/admin-scheduler.php?error=No classrooms found for subject type: {$subject['subject_type']}.");
                exit();
            }

            $requiredSlots = ceil($subject['minutes_per_week'] / ($subject['minutes_per_week'] === 90 ? 90 : 60)); // Calculate slots based on minutes_per_week
            $scheduledSlots = 0;

            // Sort days by least loaded first (to balance the schedule)
            uasort($days, function ($a, $b) use ($subjectLoad) {
                return $subjectLoad[$a] <=> $subjectLoad[$b];
            });

            foreach ($days as $day) {

                $duration = ($subject['minutes_per_week'] === 90) ? 90 : 60;
                $daySlots = generateDaySlots($schoolHours, $lunchBreak, $duration);

                $classroomUsage[$day] = $classroomUsage[$day] ?? [];

                foreach ($rooms as $room) {
                    // Skip if classroom is already used in this day
                    if (in_array($room['id'], $classroomUsage[$day])) {
                        continue;
                    }

                    foreach ($daySlots as $slot) {
                        [$start, $end] = $slot;

                        // Check for conflicts
                        if (checkConflict($conn, $day, $start, $end, $subject['teacher_id'], $room['id'], $sectionId, $semester, $academicYear, $examType)) {
                            continue;
                        }

                        // Skip if subject was already scheduled earlier that day
                        if (checkSubjectOnDay($conn, $subject['id'], $day,  $sectionId, $semester, $academicYear)) {
                            continue;
                        }


                        // Assign the schedule
                        assignSchedule($conn, $subject['id'], $subject['teacher_id'], $room['id'], $day, $start, $end, $semester, $academicYear, $examType, $subject['subject_type'], $sectionId);
                        $scheduledSlots++;

                        // Track subject and classroom usage
                        $daySubjectHistory[$day][] = $subject['id'];
                        $classroomUsage[$day][] = $room['id'];

                        // Increment the subject load for this day
                        $subjectLoad[$day]++;

                        // Break if all required slots are scheduled
                        if ($scheduledSlots >= $requiredSlots) {
                            break 3; // Exit both inner loops
                        }
                    }
                }
            }
        }
    }

    header("Location: ../views/admin/admin-scheduler.php?msg=Schedules generated successfully.");
    exit();
}
