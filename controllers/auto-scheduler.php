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

// === HELPERS ===
function getSectionInfo($conn, $sectionId)
{
    $stmt = $conn->prepare("SELECT department, year_level, semester FROM sections WHERE id = ?");
    $stmt->bind_param('i', $sectionId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getSubjectsForSection($conn, $sectionId, $semester, $academicYear)
{
    $sectionInfo = getSectionInfo($conn, $sectionId);
    $sectionDept = $sectionInfo['department'];
    $sectionYearLevel = $sectionInfo['year_level'];
    $sectionSemester = $sectionInfo['semester'];

    // Allow all sections to include General_Education subjects
    $allowedSubjectDepts = ['General_Education'];

    switch ($sectionDept) {
        case 'BSIT':
            $allowedSubjectDepts = array_merge($allowedSubjectDepts, ['BSIT']);
            break;
        case 'BSED_Mathematics':
            $allowedSubjectDepts = array_merge($allowedSubjectDepts, ['BSED_Mathematics', 'BSED_All']);
            break;
        case 'BSED_Social_Studies':
            $allowedSubjectDepts = array_merge($allowedSubjectDepts, ['BSED_Social_Studies', 'BSED_All']);
            break;
        case 'BSED_All':
            $allowedSubjectDepts = array_merge($allowedSubjectDepts, ['BSED_All']);
            break;
        case 'BTVTED_Garments':
            $allowedSubjectDepts = array_merge($allowedSubjectDepts, ['BTVTED_Garments', 'BTVTED_All']);
            break;
        case 'BTVTED_Electronics':
            $allowedSubjectDepts = array_merge($allowedSubjectDepts, ['BTVTED_Electronics', 'BTVTED_All']);
            break;
        case 'BTVTED_Electrical':
            $allowedSubjectDepts = array_merge($allowedSubjectDepts, ['BTVTED_Electrical', 'BTVTED_All']);
            break;
        case 'BTVTED_All':
            $allowedSubjectDepts = array_merge($allowedSubjectDepts, ['BTVTED_All']);
            break;
        case 'BAT_Crops_Production':
            $allowedSubjectDepts = array_merge($allowedSubjectDepts, ['BAT_Crops_Production']);
            break;
        case 'BSA_Agronomy':
            $allowedSubjectDepts = array_merge($allowedSubjectDepts, ['BSA_Agronomy']);
            break;
        default:
            // Keep only General_Education
            break;
    }

    $deptList = "'" . implode("','", $allowedSubjectDepts) . "'";

    $sql = "
        SELECT s.*, ts.teacher_id
        FROM (
            SELECT subjects.*, 
                   CASE WHEN subjects.subject_type = 'pe' THEN 1 ELSE 0 END as is_pe
        FROM subjects
        INNER JOIN sections ON subjects.semester = sections.semester
        WHERE sections.id = ?
        AND subjects.semester = ?
        AND subjects.year_level = ?
        AND subjects.department IN ($deptList)
        ) s
        LEFT JOIN teacher_subjects ts ON s.id = ts.subject_id AND ts.academic_year = ?
        GROUP BY s.id, s.is_pe
        HAVING (s.is_pe = 0) OR (s.is_pe = 1 AND (ts.teacher_id IS NULL OR ts.teacher_id = MIN(ts.teacher_id)))
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isis', $sectionId, $semester, $sectionYearLevel, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getClassrooms($conn, $subjectType, $sectionDept = null)
{
    $type = match ($subjectType) {
        'lab' => 'Laboratory',
        'pe' => 'Gym',
        default => 'Room'
    };

    // ========== PE CLASSES (Gym) ==========
    if ($subjectType === 'pe') {
        // Allow multiple sections to share the same gym
        $stmt = $conn->prepare("
            SELECT id, room_number, capacity 
            FROM classrooms 
            WHERE type = 'Gym' 
            AND department = 'GENERAL'
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ========== LAB CLASSES ==========
    if ($subjectType === 'lab') {
        // BSED departments - use regular rooms (no labs)
        if (in_array($sectionDept, ['BSED_Mathematics', 'BSED_Social_Studies', 'BSED_All'])) {
            $stmt = $conn->prepare("
                SELECT id, room_number, capacity 
                FROM classrooms 
                WHERE type = 'Room' 
                AND department = 'BSED'
            ");
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        // All other departments - use lab in their department
        $labDept = match ($sectionDept) {
            'BSIT' => 'BSIT',
            'BTVTED_Garments', 'BTVTED_Electronics', 'BTVTED_Electrical', 'BTVTED_All' => 'BTVTED',
            'Diploma_Agricultural_Sciences', 'BAT_Crops_Production', 'BSA_Agronomy' => 'DAT-BAT',
            default => 'GENERAL'
        };

        $stmt = $conn->prepare("
            SELECT id, room_number, capacity 
            FROM classrooms 
            WHERE type = 'Laboratory' 
            AND department = ?
        ");
        $stmt->bind_param('s', $labDept);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ========== LECTURE CLASSES ==========
    // For lecture subjects, use department-specific rooms
    $allowedClassroomDept = match ($sectionDept) {
        'BSIT' => 'BSIT',
        'BSED_Mathematics', 'BSED_Social_Studies', 'BSED_All' => 'BSED',
        'BTVTED_Garments', 'BTVTED_Electronics', 'BTVTED_Electrical', 'BTVTED_All' => 'BTVTED',
        'BAT_Crops_Production', 'BSA_Agronomy' => 'DAT-BAT',
        default => 'GENERAL'
    };

    $stmt = $conn->prepare("
        SELECT id, room_number, capacity 
        FROM classrooms 
        WHERE type = ? 
        AND department = ?
    ");
    $stmt->bind_param('ss', $type, $allowedClassroomDept);
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

function checkConflict($conn, $day, $start, $end, $teacherId, $roomId, $sectionId, $semester, $academicYear, $examType, $subjectType)
{
    // For PE classes, only check teacher and section conflicts
    if ($subjectType === 'pe') {
        $stmt = $conn->prepare("
            SELECT 1 FROM schedules 
            WHERE day = ? AND exam_type = ?
            AND (
                (start_time < ? AND end_time > ?) OR
                (start_time < ? AND end_time > ?) OR
                (start_time >= ? AND end_time <= ?)
            )
            AND (
                teacher_id = ? OR 
                section_id = ?
            )
            AND semester = ? AND academic_year = ?
        ");
        $stmt->bind_param(
            'ssssssssiiss',
            $day,
            $examType,
            $end,
            $start,
            $start,
            $end,
            $start,
            $end,
            $teacherId,
            $sectionId,
            $semester,
            $academicYear
        );
    } else {
        // Normal conflict checking for other subjects
        $stmt = $conn->prepare("
            SELECT 1 FROM schedules 
            WHERE day = ? AND exam_type = ?
            AND (
                (start_time < ? AND end_time > ?) OR
                (start_time < ? AND end_time > ?) OR
                (start_time >= ? AND end_time <= ?)
            )
            AND (
                teacher_id = ? OR 
                classroom_id = ? OR 
                section_id = ?
            )
            AND semester = ? AND academic_year = ?
        ");
        $stmt->bind_param(
            'ssssssssiiiis',
            $day,
            $examType,
            $end,
            $start,
            $start,
            $end,
            $start,
            $end,
            $teacherId,
            $roomId,
            $sectionId,
            $semester,
            $academicYear
        );
    }
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

function checkSubjectOnDay($conn, $subjectId, $day, $sectionId, $semester, $academicYear, $examType)
{
    $stmt = $conn->prepare("
        SELECT 1 FROM schedules 
        WHERE subject_id = ? AND day = ? AND section_id = ? AND semester = ? AND academic_year = ? AND exam_type = ?
    ");
    $stmt->bind_param('isssss', $subjectId, $day, $sectionId, $semester, $academicYear, $examType);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getTeachers($conn, $semester, $academicYear)
{
    $stmt = $conn->prepare("SELECT * FROM teacher_subjects WHERE semester = ? AND academic_year = ?");
    $stmt->bind_param('ss', $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getExistingSchedules($conn, $semester, $academicYear, $examType)
{
    $stmt = $conn->prepare("SELECT * FROM schedules WHERE semester = ? AND academic_year = ? AND exam_type = ?");
    $stmt->bind_param('sss', $semester, $academicYear, $examType);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Updated generateDaySlots function for more flexible scheduling
function generateFlexibleSlots($startHour, $endHour, $lunchBreak, $duration)
{
    $slots = [];
    $startTime = strtotime($startHour);
    $endTime = strtotime($endHour);
    $lunchStart = strtotime($lunchBreak[0]);
    $lunchEnd = strtotime($lunchBreak[1]);

    $currentTime = $startTime;

    while ($currentTime + ($duration * 60) <= $endTime) {
        $slotEnd = $currentTime + ($duration * 60);

        // Skip lunch break
        if ($currentTime < $lunchStart && $slotEnd > $lunchStart) {
            $currentTime = $lunchEnd;
            continue;
        }

        // Skip if during lunch
        if ($currentTime >= $lunchStart && $currentTime < $lunchEnd) {
            $currentTime = $lunchEnd;
            continue;
        }

        $slots[] = [
            date('H:i', $currentTime),
            date('H:i', $slotEnd)
        ];

        // Move to next possible start time (minimum 10 minute gap)
        $currentTime = $slotEnd + (10 * 60);
    }

    return $slots;
}
// === MAIN SCHEDULE LOGIC ===
if (isset($_POST['submit'])) {
    $semester = $_POST['semester'];
    $examType = $_POST['type'];
    $academicYear = $_POST['academic_year'];

    // Check for existing schedules
    $existingSchedules = getExistingSchedules($conn, $semester, $academicYear, $examType);
    if (!empty($existingSchedules)) {
        if ($examType === 'none') {
            header("Location: ../views/admin/admin-scheduler.php?error=Class Schedules already exist for the selected semester and academic year.");
            exit();
        } else {
            header("Location: ../views/admin/admin-scheduler.php?error=Exam Schedules already exist for the selected semester and academic year.");
            exit();
        }
    }

    $teacherSubjects = getTeachers($conn, $semester, $academicYear);

    $sections = getSections($conn, $semester, $academicYear);

    if (empty($sections)) {
        header("Location: ../views/admin/admin-scheduler.php?error=No sections found.");
        exit();
    }


    // Updated main scheduling logic
    // Updated main scheduling logic with optimizations
    if ($examType === 'none') {
        // === CONFIGURATION ===
        $schoolDayStart = '08:00 AM';
        $schoolDayEnd = '05:00 PM';
        $lunchBreak = ['12:00 PM', '01:00 PM'];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        // Track scheduling results
        $subjectsWithoutTeachers = [];
        $subjectsNotScheduled = [];
        $sectionsScheduled = [];

        // Get all classrooms once for optimization
        $allClassrooms = [];
        foreach ($sections as $section) {
            $sectionDept = $section['department'];
            $allClassrooms['lecture'][$sectionDept] = getClassrooms($conn, 'lecture', $sectionDept);
            $allClassrooms['lab'][$sectionDept] = getClassrooms($conn, 'lab', $sectionDept);
            $allClassrooms['pe'][$sectionDept] = getClassrooms($conn, 'pe', $sectionDept);
        }

        // Sort sections by priority (higher years first, then smaller sections)
        usort($sections, function ($a, $b) {
            $yearPriority = $b['year_level'] <=> $a['year_level'];
            if ($yearPriority !== 0) return $yearPriority;
            return $a['student_count'] <=> $b['student_count']; // Smaller sections first
        });

        foreach ($sections as $section) {
            $sectionId = $section['id'];
            $sectionDept = $section['department'];
            $sectionYearLevel = $section['year_level'];
            $sectionSemester = $section['semester'];

            $subjects = getSubjectsForSection($conn, $sectionId, $sectionSemester, $academicYear);

            // Filter and track subjects without teachers
            $subjectsWithTeachers = [];
            foreach ($subjects as $subject) {
                if ($subject['teacher_id'] === null) {
                    $subjectsWithoutTeachers[] = [
                        'subject_code' => $subject['subject_code'],
                        'subject_name' => $subject['subject_name'],
                        'section_id' => $sectionId,
                        'section_name' => $section['section_name'] ?? "Section {$sectionId}"
                    ];
                } else {
                    $subjectsWithTeachers[] = $subject;
                }
            }

            $daySubjectHistory = [];
            $classroomUsage = [];
            $subjectLoad = array_fill_keys($days, 0);
            $sectionSubjectsScheduled = [];

            // Sort subjects by constraints (PE first, then labs, then lectures with more weekly minutes first)
            usort($subjectsWithTeachers, function ($a, $b) {
                $priority = ['pe' => 1, 'lab' => 2, 'lecture' => 3];
                if ($priority[$a['subject_type']] !== $priority[$b['subject_type']]) {
                    return $priority[$a['subject_type']] <=> $priority[$b['subject_type']];
                }
                return $b['minutes_per_week'] <=> $a['minutes_per_week']; // More minutes first
            });

            foreach ($subjectsWithTeachers as $subject) {
                $rooms = $allClassrooms[$subject['subject_type']][$sectionDept] ?? [];
                if (empty($rooms) && $subject['subject_type'] !== 'lecture') {
                    $subjectsNotScheduled[] = [
                        'subject_code' => $subject['subject_code'],
                        'subject_name' => $subject['subject_name'],
                        'section_id' => $sectionId,
                        'section_name' => $section['section_name'] ?? "Section {$sectionId}",
                        'reason' => 'No classroom available'
                    ];
                    continue;
                }

                // Determine duration and required slots
                if ($subject['subject_type'] === 'lecture') {
                    $duration = 60;
                    $requiredSlots = ceil($subject['minutes_per_week'] / $duration);
                    $maxDaysBetweenSlots = 2; // Try to spread lectures across week
                } elseif ($subject['subject_type'] === 'pe') {
                    $duration = 120;
                    $requiredSlots = 1;
                    $maxDaysBetweenSlots = 7; // No restriction
                } else { // lab
                    $duration = $subject['minutes_per_week'];
                    $requiredSlots = 1;
                    $maxDaysBetweenSlots = 7; // No restriction
                }

                $scheduledSlots = 0;
                $daysAttempted = 0;

                // Try days in different orders to find best fit
                $dayOrders = [
                    ['Monday', 'Wednesday', 'Friday', 'Tuesday', 'Thursday'], // MWF first
                    ['Tuesday', 'Thursday', 'Monday', 'Wednesday', 'Friday'], // TTh first
                    array_reverse($days) // Reverse order
                ];

                foreach ($dayOrders as $dayOrder) {
                    if ($scheduledSlots >= $requiredSlots) break;

                    // Try each day until we find available slots
                    foreach ($dayOrder as $day) {
                        if ($scheduledSlots >= $requiredSlots) break;
                        if ($daysAttempted >= 5) break; // Don't keep trying if we've checked all days

                        // Skip if we already have this subject on this day
                        if (isset($daySubjectHistory[$day]) && in_array($subject['id'], $daySubjectHistory[$day])) {
                            continue;
                        }

                        // Generate all possible slots for this day
                        $daySlots = generateFlexibleSlots($schoolDayStart, $schoolDayEnd, $lunchBreak, $duration);

                        // Try each room until we find an available one
                        shuffle($rooms); // Randomize room order to distribute usage
                        foreach ($rooms as $room) {
                            if ($scheduledSlots >= $requiredSlots) break;

                            // Try each time slot
                            foreach ($daySlots as $slot) {
                                [$start, $end] = $slot;

                                // Check for conflicts
                                if (
                                    !checkConflict($conn, $day, $start, $end, $subject['teacher_id'], $room['id'], $sectionId, $semester, $academicYear, $examType, $subject['subject_type']) &&
                                    !checkSubjectOnDay($conn, $subject['id'], $day, $sectionId, $semester, $academicYear, $examType)
                                ) {
                                    assignSchedule($conn, $subject['id'], $subject['teacher_id'], $room['id'], $day, $start, $end, $semester, $academicYear, $examType, $subject['subject_type'], $sectionId);
                                    $scheduledSlots++;
                                    $daySubjectHistory[$day][] = $subject['id'];
                                    $classroomUsage[$day][] = $room['id'];
                                    $subjectLoad[$day]++;
                                    $sectionSubjectsScheduled[$subject['id']] = true;
                                    $daysAttempted++;

                                    if ($scheduledSlots >= $requiredSlots) break 3;
                                }
                            }
                        }
                    }
                }

                // Track if not fully scheduled
                if ($scheduledSlots < $requiredSlots) {
                    $subjectsNotScheduled[] = [
                        'subject_code' => $subject['subject_code'],
                        'subject_name' => $subject['subject_name'],
                        'section_id' => $sectionId,
                        'section_name' => $section['section_name'] ?? "Section {$sectionId}",
                        'reason' => 'Could not find available time slot after multiple attempts',
                        'teacher_id' => $subject['teacher_id'],
                        'subject_type' => $subject['subject_type'],
                        'minutes_per_week' => $subject['minutes_per_week']
                    ];
                }
            }

            $sectionsScheduled[] = $sectionId;
        }

        // Prepare and store report
        $report = [
            'sections_scheduled' => count($sectionsScheduled),
            'subjects_without_teachers' => $subjectsWithoutTeachers,
            'subjects_not_scheduled' => $subjectsNotScheduled,
            'subjects_not_scheduled_count' => count($subjectsNotScheduled),
            'total_subjects_attempted' => count($subjectsWithTeachers) + count($subjectsWithoutTeachers)
        ];
        session_start();
        $_SESSION['scheduling_report'] = $report;

        header("Location: ../views/admin/admin-scheduler.php?msg=Class Schedules generated successfully with " . count($subjectsNotScheduled) . " unscheduled subjects.");
        exit();
    }
}
