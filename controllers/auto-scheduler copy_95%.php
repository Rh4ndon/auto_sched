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
        case 'Diploma_Agricultural_Sciences':
            $allowedSubjectDepts = array_merge($allowedSubjectDepts, ['Diploma_Agricultural_Sciences']);
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

function getSectionDepartment($conn, $sectionId)
{
    $stmt = $conn->prepare("SELECT department FROM sections WHERE id = ?");
    $stmt->bind_param('i', $sectionId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['department'] ?? null;
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
        'Diploma_Agricultural_Sciences', 'BAT_Crops_Production', 'BSA_Agronomy' => 'DAT-BAT',
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

function checkFridayConflict($conn, $day, $start, $end, $teacherId, $sectionId, $semester, $academicYear, $examType)
{
    // If no teacher assigned, no conflict possible
    if ($teacherId === null) {
        return false;
    }

    $stmt = $conn->prepare("
        SELECT 1 FROM schedules 
        WHERE day = ? AND exam_type = ?
        AND (
            (start_time < ? AND end_time > ?) OR 
            (start_time < ? AND end_time > ?)
        )
        AND teacher_id = ? AND section_id = ?
        AND semester = ? AND academic_year = ?
    ");
    $stmt->bind_param('ssssssiiss', $day, $examType, $end, $start, $start, $end, $teacherId, $sectionId, $semester, $academicYear);
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

// Add this new function for assigning online schedules
function assignOnlineSchedule($conn, $subjectId, $teacherId, $day, $start, $end, $semester, $academicYear, $sectionId)
{
    $stmt = $conn->prepare("
        INSERT INTO schedules 
        (subject_id, teacher_id, classroom_id, section_id, day, start_time, end_time, semester, academic_year, exam_type, subject_type)
        VALUES (?, ?, 0, ?, ?, ?, ?, ?, ?, 'none', 'online')
    ");
    $stmt->bind_param(
        'iiisssss',
        $subjectId,
        $teacherId,
        $sectionId,
        $day,
        $start,
        $end,
        $semester,
        $academicYear
    );
    if (!$stmt->execute()) {
        die("Failed to insert online schedule: " . $stmt->error);
    }
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

    $sectionDepartments = [
        'BSIT',
        'BSED_Mathematics',
        'BSED_Social_Studies',
        'BSED_All',
        'BTVTED_Garments',
        'BTVTED_Electronics',
        'BTVTED_Electrical',
        'BTVTED_All',
        'Diploma_Agricultural_Sciences',
        'BAT_Crops_Production',
        'BSA_Agronomy'
    ];

    if (empty($sections)) {
        header("Location: ../views/admin/admin-scheduler.php?error=No sections found.");
        exit();
    }

    if ($examType === 'none') {
        // === CONFIGURATION ===
        $lectureSlots = [
            ['07:00 AM', '09:00 AM'],
            ['09:30 AM', '11:30 AM'],
            ['12:30 PM', '02:30 PM'],
            ['03:00 PM', '05:00 PM']
        ];

        $labSlots = [
            ['07:00 AM', '10:00 AM'],
            ['01:00 PM', '04:00 PM']
        ];

        $onlineHours = [
            ['07:00 AM', '08:00 AM'],
            ['08:00 AM', '09:00 AM'],
            ['09:00 AM', '10:00 AM'],
            ['10:00 AM', '11:00 AM'],
            ['11:00 AM', '12:00 PM'],
            ['01:00 PM', '02:00 PM'],
            ['02:00 PM', '03:00 PM'],
            ['04:00 PM', '05:00 PM']
        ];
        $lunchBreak = ['12:00 PM', '01:00 PM'];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        // Track subjects without teachers for reporting
        $subjectsWithoutTeachers = [];

        // Track online slots globally for all sections
        $globalFridayOnlineSlots = [];

        // Sort sections by lab requirements (highest first)
        usort($sections, function ($a, $b) {
            $aPriority = match ($a['year_level']) {
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                default => 0
            };
            $bPriority = match ($b['year_level']) {
                '1' => 1,
                '2' => 2,
                '3' => 3,
                '4' => 4,
                default => 0
            };
            return $bPriority <=> $aPriority;
        });


        foreach ($sections as $section) {
            $sectionId = $section['id'];
            $sectionYearLevel = $section['year_level'];
            $sectionSemester = $section['semester'];

            $subjects = getSubjectsForSection($conn, $sectionId, $sectionSemester, $academicYear);

            // Filter out subjects without teachers and track them
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

            $daySubjectHistory = []; // Track subjects assigned per day
            $classroomUsage = []; // Track classroom usage per day
            $subjectLoad = array_fill_keys($days, 0); // Track the number of subjects scheduled per day
            $sectionFridayOnlineSlots = []; // Track slots used by this section

            shuffle($subjectsWithTeachers); // Randomize subjects with teachers only

            foreach ($subjectsWithTeachers as $subject) {
                $sectionDept = $section['department'];
                $rooms = getClassrooms($conn, $subject['subject_type'], $sectionDept);

                if (empty($rooms) && $subject['subject_type'] !== 'lecture') {
                    header("Location: ../views/admin/admin-scheduler.php?error=No classrooms found for subject type: {$subject['subject_type']}.");
                    exit();
                }

                // Calculate required slots (for lecture, we subtract 60 minutes for online class)
                if ($subject['subject_type'] === 'lecture') {
                    $requiredSlots = ceil(($subject['minutes_per_week'] - 60) / (($subject['minutes_per_week'] - 60) === 120 ? 120 : 60));
                } else {
                    $requiredSlots = ceil($subject['minutes_per_week'] / ($subject['minutes_per_week'] === 120 ? 120 : 180));
                }
                $scheduledSlots = 0;

                // Sort days by least loaded first (to balance the schedule)
                uasort($days, function ($a, $b) use ($subjectLoad) {
                    return $subjectLoad[$a] <=> $subjectLoad[$b];
                });

                foreach ($days as $day) {
                    // Handle Friday scheduling differently
                    if ($day === 'Friday') {
                        // Only schedule lecture subjects on Friday (as online classes)
                        if ($subject['subject_type'] === 'lecture') {
                            foreach ($onlineHours as $slot) {
                                $start = date('H:i:s', strtotime($slot[0]));
                                $end = date('H:i:s', strtotime($slot[1]));

                                // Check for teacher conflicts
                                if (!checkFridayConflict($conn, $day, $start, $end, $subject['teacher_id'], $sectionId, $semester, $academicYear, $examType)) {
                                    assignOnlineSchedule($conn, $subject['id'], $subject['teacher_id'], $day, $start, $end, $semester, $academicYear, $sectionId);
                                    $scheduledSlots++;
                                    $subjectLoad[$day]++;
                                    $globalFridayOnlineSlots[] = $slot[0]; // Track globally
                                    $sectionFridayOnlineSlots[] = $slot[0]; // Track per section
                                    break;
                                }
                            }
                            continue; // Skip to next subject after attempting online scheduling
                        }
                        // Skip Friday for non-lecture subjects
                        continue;
                    }

                    // Regular scheduling for non-Friday days
                    if ($subject['subject_type'] === 'lecture') {
                        $duration = ($subject['minutes_per_week'] === 120) ? 60 : 120;
                    } elseif ($subject['subject_type'] === 'pe') {
                        $duration = 120; // PE subjects typically have 120 minutes per week
                    } else {
                        $duration = 180; // Default duration for lab subjects
                    }

                    // When scheduling, choose slots based on subject type:
                    $daySlots = ($subject['subject_type'] === 'lab')
                        ? generateDaySlots($labSlots, $lunchBreak, 180)
                        : generateDaySlots($lectureSlots, $lunchBreak, 120);

                    $classroomUsage[$day] = $classroomUsage[$day] ?? [];

                    // When assigning rooms, prioritize unused rooms first
                    usort($rooms, function ($a, $b) use ($classroomUsage, $day) {
                        $aUsed = in_array($a['id'], $classroomUsage[$day] ?? []);
                        $bUsed = in_array($b['id'], $classroomUsage[$day] ?? []);
                        return $aUsed <=> $bUsed;
                    });

                    foreach ($rooms as $room) {
                        if (in_array($room['id'], $classroomUsage[$day])) {
                            continue;
                        }

                        foreach ($daySlots as $slot) {
                            [$start, $end] = $slot;

                            if (checkConflict($conn, $day, $start, $end, $subject['teacher_id'], $room['id'], $sectionId, $semester, $academicYear, $examType, $subject['subject_type'])) {
                                continue;
                            }

                            if (checkSubjectOnDay($conn, $subject['id'], $day, $sectionId, $semester, $academicYear, $examType)) {
                                continue;
                            }

                            assignSchedule($conn, $subject['id'], $subject['teacher_id'], $room['id'], $day, $start, $end, $semester, $academicYear, $examType, $subject['subject_type'], $sectionId);
                            $scheduledSlots++;
                            $daySubjectHistory[$day][] = $subject['id'];
                            $classroomUsage[$day][] = $room['id'];
                            $subjectLoad[$day]++;

                            if ($scheduledSlots >= $requiredSlots) {
                                break 3;
                            }
                        }
                    }
                }
            }
        }


        header("Location: ../views/admin/admin-scheduler.php?msg=Class Schedules generated successfully.");
        exit();
    } else {
        // === EXAM SCHEDULING LOGIC ===


        // === CONFIGURATION ===
        $schoolHours = [
            ['07:00 AM', '08:30 AM'],
            ['09:30 AM', '11:00 AM'],
            ['01:00 PM', '02:30 PM'],
            ['03:30 PM', '05:00 PM']
        ];
        $lunchBreak = ['12:00 PM', '01:00 PM'];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        // === SEMESTER SCHEDULING LOGIC ===

        foreach ($sections as $section) {
            $sectionId = $section['id'];
            $sectionYearLevel = $section['year_level'];
            $sectionSemester = $section['semester'];

            $subjects = getSubjectsForSection($conn, $sectionId, $sectionSemester, $academicYear);

            // Filter out subjects without teachers and track them
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

            $daySubjectHistory = []; // Track subjects assigned per day
            $classroomUsage = []; // Track classroom usage per day
            $subjectLoad = array_fill_keys($days, 0); // Track the number of subjects scheduled per day
            $sectionFridayOnlineSlots = []; // Track slots used by this section

            shuffle($subjectsWithTeachers); // Randomize subjects with teachers only

            foreach ($subjectsWithTeachers as $subject) {
                if ($subject['subject_type'] === 'lab') {

                    continue; // Skip to lab subjects for exam scheduling
                }
                $sectionDept = $section['department'];
                $rooms = getClassrooms($conn, $subject['subject_type'], $sectionDept);

                if (empty($rooms) && $subject['subject_type'] !== 'lecture') {
                    header("Location: ../views/admin/admin-scheduler.php?error=No classrooms found for subject type: {$subject['subject_type']}.");
                    exit();
                }


                $requiredSlots = 1; // For exams, we only need one slot per subject

                $scheduledSlots = 0;

                // Sort days by least loaded first (to balance the schedule)
                uasort($days, function ($a, $b) use ($subjectLoad) {
                    return $subjectLoad[$a] <=> $subjectLoad[$b];
                });

                foreach ($days as $day) {



                    $duration = 90; // Default duration for exams

                    $daySlots = generateDaySlots($schoolHours, $lunchBreak, $duration);

                    $classroomUsage[$day] = $classroomUsage[$day] ?? [];

                    // When assigning rooms, prioritize unused rooms first
                    usort($rooms, function ($a, $b) use ($classroomUsage, $day) {
                        $aUsed = in_array($a['id'], $classroomUsage[$day] ?? []);
                        $bUsed = in_array($b['id'], $classroomUsage[$day] ?? []);
                        return $aUsed <=> $bUsed;
                    });

                    foreach ($rooms as $room) {
                        if (in_array($room['id'], $classroomUsage[$day])) {
                            continue;
                        }

                        foreach ($daySlots as $slot) {
                            [$start, $end] = $slot;

                            if (checkConflict($conn, $day, $start, $end, $subject['teacher_id'], $room['id'], $sectionId, $semester, $academicYear, $examType, $subject['subject_type'])) {
                                continue;
                            }

                            if (checkSubjectOnDay($conn, $subject['id'], $day, $sectionId, $semester, $academicYear, $examType)) {
                                continue;
                            }

                            assignSchedule($conn, $subject['id'], $subject['teacher_id'], $room['id'], $day, $start, $end, $semester, $academicYear, $examType, $subject['subject_type'], $sectionId);
                            $scheduledSlots++;
                            $daySubjectHistory[$day][] = $subject['id'];
                            $classroomUsage[$day][] = $room['id'];
                            $subjectLoad[$day]++;

                            if ($scheduledSlots >= $requiredSlots) {
                                break 3;
                            }
                        }
                    }
                }
            }
        }

        header("Location: ../views/admin/admin-scheduler.php?msg=Exam Schedules generated successfully.");
        exit();
    }
}
