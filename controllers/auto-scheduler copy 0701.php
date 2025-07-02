<?php
set_time_limit(600); // 300 seconds = 5 minutes

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
    $stmt = $conn->prepare("SELECT department, year_level, semester, section_name FROM sections WHERE id = ?");
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
        SELECT s.*, GROUP_CONCAT(ts.teacher_id) as teacher_ids
        FROM (
            SELECT subjects.*
            FROM subjects
            INNER JOIN sections ON subjects.semester = sections.semester
            WHERE sections.id = ?
            AND subjects.semester = ?
            AND subjects.year_level = ?
            AND subjects.department IN ($deptList)
        ) s
        LEFT JOIN teacher_subjects ts ON s.id = ts.subject_id AND ts.academic_year = ?
        GROUP BY s.id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isis', $sectionId, $semester, $sectionYearLevel, $academicYear);
    $stmt->execute();

    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Process teacher_ids into an array
    foreach ($results as &$result) {
        $result['teacher_ids'] = $result['teacher_ids'] ? explode(',', $result['teacher_ids']) : [];
    }

    return $results;
}

function getClassrooms($conn, $subjectType, $sectionDept = null, $allowFallback = false, $subjectDept = null)
{
    $type = match ($subjectType) {
        'lab' => 'Laboratory',
        'pe' => 'Gym',
        default => 'Room'
    };

    // ========== PE CLASSES (Gym) ==========
    if ($subjectType === 'pe') {
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

        // First try to get labs from the specific department
        $stmt = $conn->prepare("
            SELECT id, room_number, capacity 
            FROM classrooms 
            WHERE type = 'Laboratory' 
            AND department = ?
        ");
        $stmt->bind_param('s', $labDept);
        $stmt->execute();
        $labs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // If no labs found and fallback is allowed, try general labs
        if (empty($labs)) {
            $stmt = $conn->prepare("
                SELECT id, room_number, capacity 
                FROM classrooms 
                WHERE type = 'Laboratory' 
                AND department = 'GENERAL'
            ");
            $stmt->execute();
            $labs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        // If still no labs and fallback is allowed, try regular rooms
        if (empty($labs)) {
            $stmt = $conn->prepare("
                SELECT id, room_number, capacity 
                FROM classrooms 
                WHERE type = 'Room' 
                AND department = ?
            ");
            $stmt->bind_param('s', $labDept);
            $stmt->execute();
            $labs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        return $labs;
    }

    // ========== LECTURE CLASSES ==========
    $allowedDepts = ['GENERAL'];
    $deptSpecific = match ($sectionDept) {
        'BSIT' => 'BSIT',
        'BSED_Mathematics', 'BSED_Social_Studies', 'BSED_All' => 'BSED',
        'BTVTED_Garments', 'BTVTED_Electronics', 'BTVTED_Electrical', 'BTVTED_All' => 'BTVTED',
        'BAT_Crops_Production', 'BSA_Agronomy' => 'DAT-BAT',
        default => 'GENERAL'
    };
    $allowedDepts[] = $deptSpecific;

    // Get regular rooms first
    $rooms = [];
    $deptList = "'" . implode("','", $allowedDepts) . "'";

    $stmt = $conn->prepare("
        SELECT id, room_number, capacity 
        FROM classrooms 
        WHERE type = ? 
        AND department IN ($deptList)
        ORDER BY 
            CASE WHEN department = ? THEN 1 ELSE 2 END,
            capacity DESC
    ");
    $stmt->bind_param('ss', $type, $deptSpecific);
    $stmt->execute();
    $rooms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Special case: BSIT lecture subjects can use BSIT laboratories, but General_Education cannot
    if ($sectionDept === 'BSIT' && $subjectDept === 'BSIT') {
        $stmt = $conn->prepare("
            SELECT id, room_number, capacity 
            FROM classrooms 
            WHERE type = 'Laboratory' 
            AND department = 'BSIT'
            ORDER BY capacity DESC
        ");
        $stmt->execute();
        $labRooms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $rooms = array_merge($rooms, $labRooms);
    }

    // For lecture classes, also allow other departments' rooms if needed (fallback)
    if ($allowFallback) {
        $additionalDepts = ['BSIT', 'BSED', 'BTVTED', 'DAT-BAT'];
        $additionalDepts = array_diff($additionalDepts, $allowedDepts);

        if (!empty($additionalDepts)) {
            $additionalDeptList = "'" . implode("','", $additionalDepts) . "'";

            $stmt = $conn->prepare("
                SELECT id, room_number, capacity 
                FROM classrooms 
                WHERE type = ? 
                AND department IN ($additionalDeptList)
                ORDER BY capacity DESC
            ");
            $stmt->bind_param('s', $type);
            $stmt->execute();
            $additionalRooms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $rooms = array_merge($rooms, $additionalRooms);
        }
    }

    return $rooms;
}

function getSections($conn, $semester, $academicYear)
{
    $stmt = $conn->prepare("SELECT * FROM sections WHERE semester = ? AND academic_year = ?");
    $stmt->bind_param('ss', $semester, $academicYear);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function checkConflict($conn, $day, $start, $end, $teacherId, $roomId, $sectionId, $semester, $academicYear, $examType, $subjectType, $subjectDept = null, $subjectId = null)
{
    // Get section department
    $sectionInfo = getSectionInfo($conn, $sectionId);
    $sectionDept = $sectionInfo['department'];

    // For PE classes, only check teacher and section conflicts (rooms can be shared)
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
    }
    // Special case for BSIT subjects using BSIT laboratories
    elseif ($sectionDept === 'BSIT' && $subjectType === 'lecture' && $subjectDept === 'BSIT') {
        // Check if the room is a BSIT laboratory
        $stmt = $conn->prepare("SELECT type, department FROM classrooms WHERE id = ?");
        $stmt->bind_param('i', $roomId);
        $stmt->execute();
        $roomInfo = $stmt->get_result()->fetch_assoc();

        if ($roomInfo && $roomInfo['type'] === 'Laboratory' && $roomInfo['department'] === 'BSIT') {
            // For BSIT lectures in BSIT labs, allow sharing but prioritize labs over lectures
            $stmt = $conn->prepare("
                SELECT 1 FROM schedules 
                INNER JOIN subjects ON schedules.subject_id = subjects.id
                WHERE schedules.day = ? AND schedules.exam_type = ?
                AND (
                    (schedules.start_time < ? AND schedules.end_time > ?) OR
                    (schedules.start_time < ? AND schedules.end_time > ?) OR
                    (schedules.start_time >= ? AND schedules.end_time <= ?)
                )
                AND (
                    -- Always conflict if same teacher or same section
                    schedules.teacher_id = ? OR 
                    schedules.section_id = ? OR
                    -- Conflict if room is occupied by a lab (labs have priority)
                    (schedules.classroom_id = ? AND subjects.subject_type = 'lab') OR
                    -- Conflict if room is occupied by different BSIT lecture subject/teacher
                    (schedules.classroom_id = ? AND subjects.subject_type = 'lecture' AND 
                     (schedules.teacher_id != ? OR schedules.subject_id != ?))
                )
                AND schedules.semester = ? AND schedules.academic_year = ?
            ");
            $stmt->bind_param(
                'sssssssssiiiiiss',
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
                $roomId,
                $roomId,
                $teacherId,
                $subjectId,
                $semester,
                $academicYear
            );
        } else {
            // Regular room, use normal conflict checking
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
    }
    // Special case for BTVTED_All subjects with BTVTED sections
    elseif (in_array($subjectDept, ['BTVTED_All', 'General_Education']) && in_array($sectionDept, ['BTVTED_All', 'BTVTED_Garments', 'BTVTED_Electrical', 'BTVTED_Electronics'])) {
        $stmt = $conn->prepare("
        SELECT 1 FROM schedules 
        INNER JOIN sections ON schedules.section_id = sections.id
        WHERE schedules.day = ? AND schedules.exam_type = ?
        AND (
            (schedules.start_time < ? AND schedules.end_time > ?) OR
            (schedules.start_time < ? AND schedules.end_time > ?) OR
            (schedules.start_time >= ? AND schedules.end_time <= ?)
        )
        AND (
            -- Conflict if same teacher is teaching different subject at same time
            (schedules.teacher_id = ? AND schedules.subject_id != ?) OR 
            -- Conflict if same section is scheduled at same time
            schedules.section_id = ? OR
            -- Conflict if room is occupied by different department or different subject/teacher
            (schedules.classroom_id = ? AND NOT (
                schedules.teacher_id = ? AND 
                schedules.subject_id = ? AND
                sections.department IN ('BTVTED_All', 'BTVTED_Garments', 'BTVTED_Electrical', 'BTVTED_Electronics')
            ))
        )
        AND schedules.semester = ? AND schedules.academic_year = ?
    ");
        $stmt->bind_param(
            'ssssssiiiiisssis',
            $day,
            $examType,
            $end,
            $start,
            $start,
            $end,
            $start,
            $end,
            $teacherId,
            $subjectId,
            $sectionId,
            $roomId,
            $teacherId,
            $subjectId,
            $semester,
            $academicYear
        );
    }
    // Special case for BSED_All subjects with BSED sections
    elseif (in_array($subjectDept, ['BSED_All', 'General_Education']) && in_array($sectionDept, ['BSED_All', 'BSED_Social_Studies', 'BSED_Mathematics'])) {
        $stmt = $conn->prepare("
        SELECT 1 FROM schedules 
        INNER JOIN sections ON schedules.section_id = sections.id
        WHERE schedules.day = ? AND schedules.exam_type = ?
        AND (
            (schedules.start_time < ? AND schedules.end_time > ?) OR
            (schedules.start_time < ? AND schedules.end_time > ?) OR
            (schedules.start_time >= ? AND schedules.end_time <= ?)
        )
        AND (
            -- Conflict if same teacher is teaching different subject at same time
            (schedules.teacher_id = ? AND schedules.subject_id != ?) OR 
            -- Conflict if same section is scheduled at same time
            schedules.section_id = ? OR
            -- Conflict if room is occupied by different department or different subject/teacher
            (schedules.classroom_id = ? AND NOT (
                schedules.teacher_id = ? AND 
                schedules.subject_id = ? AND
                sections.department IN ('BSED_All', 'BSED_Social_Studies', 'BSED_Mathematics')
            ))
        )
        AND schedules.semester = ? AND schedules.academic_year = ?
    ");
        $stmt->bind_param(
            'ssssssiiiiisssis',
            $day,
            $examType,
            $end,
            $start,
            $start,
            $end,
            $start,
            $end,
            $teacherId,
            $subjectId,
            $sectionId,
            $roomId,
            $teacherId,
            $subjectId,
            $semester,
            $academicYear
        );
    }
    // For labs, allow room sharing if they're in the same department
    elseif ($subjectType === 'lab') {
        $stmt = $conn->prepare("
            SELECT 1 FROM schedules 
            INNER JOIN sections ON schedules.section_id = sections.id
            WHERE schedules.day = ? AND schedules.exam_type = ?
            AND (
                (schedules.start_time < ? AND schedules.end_time > ?) OR
                (schedules.start_time < ? AND schedules.end_time > ?) OR
                (schedules.start_time >= ? AND schedules.end_time <= ?)
            )
            AND (
                schedules.teacher_id = ? OR 
                schedules.section_id = ? OR
                (
                    schedules.classroom_id = ? AND 
                    (
                        schedules.subject_type != 'lab' OR 
                        sections.department != ?
                    )
                )
            )
            AND schedules.semester = ? AND schedules.academic_year = ?
        ");
        $stmt->bind_param(
            'ssssssssiiisisss',
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
            $roomId,
            $sectionDept,
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

        // Use smaller gaps for better utilization (5 minutes)
        $currentTime = $slotEnd + (5 * 60);
    }

    return $slots;
}


function prioritizeSubjects($subjects)
{
    // Sort by: 1) Labs first (since they're most constrained), 2) PE, 3) By minutes per week (longer subjects first)
    usort($subjects, function ($a, $b) {
        $typePriority = ['lab' => 1, 'pe' => 2, 'lecture' => 3];

        if ($typePriority[$a['subject_type']] !== $typePriority[$b['subject_type']]) {
            return $typePriority[$a['subject_type']] <=> $typePriority[$b['subject_type']];
        }

        // Within same type, prioritize by minutes per week (longer first)
        return $b['minutes_per_week'] <=> $a['minutes_per_week'];
    });

    return $subjects;
}

function calculateOptimalDuration($minutesPerWeek, $maxDuration = 180)
{
    // Try different durations to find best fit
    $possibleDurations = [60, 90, 120, 150, 180];
    $bestFit = [60, ceil($minutesPerWeek / 60)]; // Default fallback

    foreach ($possibleDurations as $duration) {
        if ($duration > $maxDuration) continue;

        $slots = ceil($minutesPerWeek / $duration);
        $totalMinutes = $slots * $duration;

        // If we can fit exactly or with minimal overflow, use this duration
        if ($totalMinutes <= $minutesPerWeek * 1.05) { // Tightened to 5% overflow
            return [$duration, $slots];
        }

        // Track the best fit so far (closest to required minutes)
        if (abs($totalMinutes - $minutesPerWeek) < abs($bestFit[0] * $bestFit[1] - $minutesPerWeek)) {
            $bestFit = [$duration, $slots];
        }
    }

    return $bestFit;
}

function scheduleSubjectsMultiPass($conn, $sections, $semester, $academicYear, $examType, $days, $schoolDayStart, $schoolDayEnd, $btvtedDayStart, $btvtedDayEnd, $lunchBreak)
{
    $subjectsWithoutTeachers = [];
    $subjectsNotScheduled = [];
    $sectionsScheduled = [];

    // PASS 1: Schedule high-priority subjects (Labs first, then PE)
    foreach ($sections as $section) {
        $sectionId = $section['id'];
        $sectionDept = $section['department'];

        $subjects = getSubjectsForSection($conn, $sectionId, $section['semester'], $academicYear);

        // Filter subjects with teachers
        $subjectsWithTeachers = [];
        foreach ($subjects as $subject) {
            if (empty($subject['teacher_ids'])) {
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

        // Prioritize subjects (labs first)
        $subjectsWithTeachers = prioritizeSubjects($subjectsWithTeachers);

        // Schedule labs first with most flexible options
        foreach ($subjectsWithTeachers as $subject) {
            if ($subject['subject_type'] === 'lab') {
                // Try each available teacher for this subject
                foreach ($subject['teacher_ids'] as $teacherId) {
                    $subject['teacher_id'] = $teacherId;
                    $result = scheduleLabSubject($conn, $subject, $section, $days, $schoolDayStart, $schoolDayEnd, $btvtedDayStart, $btvtedDayEnd, $lunchBreak, $semester, $academicYear, $examType, true);
                    if ($result['scheduled']) {
                        break; // Move to next subject if scheduled successfully
                    }
                }

                if (!$result['scheduled']) {
                    $subjectsNotScheduled[] = $result['info'];
                }
            }
        }
    }

    // PASS 2: Schedule PE subjects
    foreach ($sections as $section) {
        $sectionId = $section['id'];
        $subjects = getSubjectsForSection($conn, $sectionId, $section['semester'], $academicYear);

        foreach ($subjects as $subject) {
            if (!empty($subject['teacher_ids']) && $subject['subject_type'] === 'pe') {
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM schedules WHERE subject_id = ? AND section_id = ? AND semester = ? AND academic_year = ? AND exam_type = ?");
                $stmt->bind_param('iisss', $subject['id'], $sectionId, $semester, $academicYear, $examType);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();

                if ($result['count'] == 0) {
                    // Try each available teacher for this subject
                    foreach ($subject['teacher_ids'] as $teacherId) {
                        $subject['teacher_id'] = $teacherId;
                        $result = scheduleSubjectWithFallback($conn, $subject, $section, $days, $schoolDayStart, $schoolDayEnd, $btvtedDayStart, $btvtedDayEnd, $lunchBreak, $semester, $academicYear, $examType, 0);
                        if ($result['scheduled']) {
                            break; // Move to next subject if scheduled successfully
                        }
                    }

                    if (!$result['scheduled']) {
                        $subjectsNotScheduled[] = $result['info'];
                    }
                }
            }
        }
    }

    // PASS 3: Schedule lecture subjects
    foreach ($sections as $section) {
        $sectionId = $section['id'];
        $subjects = getSubjectsForSection($conn, $sectionId, $section['semester'], $academicYear);

        foreach ($subjects as $subject) {
            if (!empty($subject['teacher_ids']) && $subject['subject_type'] === 'lecture') {
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM schedules WHERE subject_id = ? AND section_id = ? AND semester = ? AND academic_year = ? AND exam_type = ?");
                $stmt->bind_param('iisss', $subject['id'], $sectionId, $semester, $academicYear, $examType);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();

                if ($result['count'] == 0) {
                    // Try each available teacher for this subject
                    foreach ($subject['teacher_ids'] as $teacherId) {
                        $subject['teacher_id'] = $teacherId;
                        $result = scheduleSubjectWithFallback($conn, $subject, $section, $days, $schoolDayStart, $schoolDayEnd, $btvtedDayStart, $btvtedDayEnd, $lunchBreak, $semester, $academicYear, $examType, 0);
                        if ($result['scheduled']) {
                            break; // Move to next subject if scheduled successfully
                        }
                    }

                    if (!$result['scheduled']) {
                        $subjectsNotScheduled[] = $result['info'];
                    }
                }
            }
        }

        $sectionsScheduled[] = $sectionId;
    }

    // PASS 4: Aggressive rescheduling for any remaining unscheduled subjects
    if (!empty($subjectsNotScheduled)) {
        $attempts = 0;
        $maxAttempts = 5; // Up to 5 attempts to reschedule

        while ($attempts < $maxAttempts && !empty($subjectsNotScheduled)) {
            $remainingSubjects = [];

            foreach ($subjectsNotScheduled as $subjectInfo) {
                // Get the full subject data
                $subject = null;
                $section = null;

                foreach ($sections as $sec) {
                    if ($sec['id'] == $subjectInfo['section_id']) {
                        $section = $sec;
                        $subjects = getSubjectsForSection($conn, $sec['id'], $sec['semester'], $academicYear);
                        foreach ($subjects as $subj) {
                            if ($subj['id'] == $subjectInfo['subject_id']) {
                                $subject = $subj;
                                break;
                            }
                        }
                        break;
                    }
                }

                if ($subject && $section) {
                    // Try each available teacher for this subject
                    foreach ($subject['teacher_ids'] as $teacherId) {
                        $subject['teacher_id'] = $teacherId;

                        // Special handling for labs
                        if ($subject['subject_type'] === 'lab') {
                            $result = scheduleLabSubject($conn, $subject, $section, $days, $schoolDayStart, $schoolDayEnd, $btvtedDayStart, $btvtedDayEnd, $lunchBreak, $semester, $academicYear, $examType, true);
                        } else {
                            $result = scheduleSubjectWithFallback($conn, $subject, $section, $days, $schoolDayStart, $schoolDayEnd, $btvtedDayStart, $btvtedDayEnd, $lunchBreak, $semester, $academicYear, $examType, $attempts + 1);
                        }

                        if ($result['scheduled']) {
                            break; // Move to next subject if scheduled successfully
                        }
                    }

                    if (!$result['scheduled']) {
                        $remainingSubjects[] = $subjectInfo;
                    }
                }
            }

            $subjectsNotScheduled = $remainingSubjects;
            $attempts++;
        }
    }

    // PASS 5: Last resort - schedule in Gym without checking for room conflicts
    if (!empty($subjectsNotScheduled)) {
        $gymRooms = getClassrooms($conn, 'pe', null, false);
        if (!empty($gymRooms)) {
            $gymRoom = $gymRooms[0]; // Just use the first gym room

            $remainingSubjects = [];

            foreach ($subjectsNotScheduled as $subjectInfo) {
                // Get the full subject data
                $subject = null;
                $section = null;

                foreach ($sections as $sec) {
                    if ($sec['id'] == $subjectInfo['section_id']) {
                        $section = $sec;
                        $subjects = getSubjectsForSection($conn, $sec['id'], $sec['semester'], $academicYear);
                        foreach ($subjects as $subj) {
                            if ($subj['id'] == $subjectInfo['subject_id']) {
                                $subject = $subj;
                                break;
                            }
                        }
                        break;
                    }
                }

                if ($subject && $section) {
                    $scheduled = false;

                    // Try each available teacher for this subject
                    foreach ($subject['teacher_ids'] as $teacherId) {
                        $subject['teacher_id'] = $teacherId;

                        // Determine duration and required slots
                        if ($subject['subject_type'] === 'lecture') {
                            list($duration, $requiredSlots) = calculateOptimalDuration($subject['minutes_per_week'], 60);
                        } else {
                            $duration = $subject['minutes_per_week'];
                            $requiredSlots = 1;
                        }

                        $scheduledSlots = 0;

                        // Try all days and time slots
                        foreach ($days as $day) {
                            if ($scheduledSlots >= $requiredSlots) break;

                            // Use extended hours
                            $daySlots = generateFlexibleSlots('07:00 AM', '06:00 PM', $lunchBreak, $duration);

                            foreach ($daySlots as $slot) {
                                [$start, $end] = $slot;

                                // Only check for teacher and section conflicts, not room conflicts
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
                                    $section['id'],
                                    $semester,
                                    $academicYear
                                );
                                $stmt->execute();

                                if ($stmt->get_result()->num_rows == 0) {
                                    // No conflicts, assign to gym
                                    assignSchedule($conn, $subject['id'], $teacherId, $gymRoom['id'], $day, $start, $end, $semester, $academicYear, $examType, $subject['subject_type'], $section['id']);
                                    $scheduledSlots++;

                                    if ($scheduledSlots >= $requiredSlots) {
                                        $scheduled = true;
                                        break 3;
                                    }
                                }
                            }
                        }
                    }

                    if (!$scheduled) {
                        $subjectInfo['reason'] = 'Could not schedule even in Gym (teacher/section conflicts)';
                        $remainingSubjects[] = $subjectInfo;
                    }
                } else {
                    $remainingSubjects[] = $subjectInfo;
                }
            }

            $subjectsNotScheduled = $remainingSubjects;
        }
    }

    return [
        'sections_scheduled' => count($sectionsScheduled),
        'subjects_without_teachers' => $subjectsWithoutTeachers,
        'subjects_not_scheduled' => $subjectsNotScheduled,
        'subjects_not_scheduled_count' => count($subjectsNotScheduled)
    ];
}




function scheduleLabSubject($conn, $subject, $section, $days, $schoolDayStart, $schoolDayEnd, $btvtedDayStart, $btvtedDayEnd, $lunchBreak, $semester, $academicYear, $examType, $allowRoomFallback)
{
    $sectionId = $section['id'];
    $sectionDept = $section['department'];
    $totalMinutes = $subject['minutes_per_week'];

    // Try different room options with decreasing priority
    $roomOptions = [
        ['type' => 'lab', 'fallback' => false], // First try department-specific labs
        ['type' => 'lab', 'fallback' => true],  // Then try any labs
        ['type' => 'lecture', 'fallback' => true] // Finally try regular rooms
    ];

    foreach ($roomOptions as $option) {
        if (!$allowRoomFallback && $option['fallback']) continue;

        $rooms = getClassrooms($conn, $option['type'], $sectionDept, $option['fallback'], $subject['department']);
        if (empty($rooms)) continue;

        // Try different scheduling patterns
        $schedulingPatterns = [
            // Pattern 1: 3-hour block (preferred)
            [
                'description' => 'Single 3-hour block',
                'blocks' => [180],
                'maxAttempts' => 3
            ],
            // Pattern 2: 2-hour + 1-hour (split session)
            [
                'description' => '2-hour + 1-hour split',
                'blocks' => [120, 60],
                'maxAttempts' => 5,
                'sameDay' => true // Try to schedule both blocks on same day
            ]
        ];

        foreach ($schedulingPatterns as $pattern) {
            // Verify this pattern matches our total minutes
            if (array_sum($pattern['blocks']) != $totalMinutes) continue;

            // Try multiple rooms
            foreach ($rooms as $room) {
                $attempts = 0;
                $scheduledBlocks = 0;
                $scheduledTimes = [];

                // Extended day range for labs
                $dayStart = '07:00 AM';
                $dayEnd = '06:00 PM';
                $lunchStart = strtotime($lunchBreak[0]);
                $lunchEnd = strtotime($lunchBreak[1]);

                // Try multiple days
                $dayOrder = $days;
                shuffle($dayOrder); // Randomize day order for better distribution

                foreach ($dayOrder as $day) {
                    if ($scheduledBlocks >= count($pattern['blocks'])) break;

                    // Generate all possible time slots for this day
                    $daySlots = [];
                    foreach ($pattern['blocks'] as $blockIndex => $duration) {
                        // For split sessions, ensure the 1-hour block comes after the 2-hour block
                        if ($pattern['description'] === '2-hour + 1-hour split' && $blockIndex === 1) {
                            // Try to schedule the 1-hour block right after the 2-hour block
                            if (!empty($scheduledTimes) && $scheduledTimes[0]['day'] === $day) {
                                $lastEnd = strtotime($scheduledTimes[0]['end']);
                                $potentialStart = date('H:i', $lastEnd + (5 * 60)); // 5 minutes after previous block
                                $potentialEnd = date('H:i', strtotime($potentialStart) + ($duration * 60));

                                // Check if this would cross lunch
                                if (!(strtotime($potentialStart) < $lunchEnd && strtotime($potentialEnd) > $lunchStart)) {
                                    $daySlots[] = [
                                        'duration' => $duration,
                                        'start' => $potentialStart,
                                        'end' => $potentialEnd,
                                        'day' => $day
                                    ];
                                    continue;
                                }
                            }
                        }

                        // Generate regular slots
                        $slots = generateFlexibleLabSlots($dayStart, $dayEnd, $lunchBreak, $duration);
                        foreach ($slots as $slot) {
                            $daySlots[] = [
                                'duration' => $duration,
                                'start' => $slot[0],
                                'end' => $slot[1],
                                'day' => $day
                            ];
                        }
                    }

                    // Sort slots by start time
                    usort($daySlots, function ($a, $b) {
                        return strtotime($a['start']) - strtotime($b['start']);
                    });

                    // Try to schedule blocks
                    foreach ($daySlots as $slot) {
                        if ($scheduledBlocks >= count($pattern['blocks'])) break;
                        if ($attempts >= $pattern['maxAttempts']) break 2;

                        // For same-day patterns, check if we should schedule on this day
                        if (
                            !empty($pattern['sameDay']) && !empty($scheduledTimes) &&
                            $scheduledTimes[0]['day'] !== $day
                        ) {
                            continue;
                        }

                        // Check if this slot conflicts with already scheduled blocks
                        $conflict = false;
                        foreach ($scheduledTimes as $scheduled) {
                            if ($scheduled['day'] === $day && (
                                (strtotime($slot['start']) < strtotime($scheduled['end']) &&
                                    strtotime($slot['end']) > strtotime($scheduled['start']))
                            )) {
                                $conflict = true;
                                break;
                            }
                        }

                        if ($conflict) continue;

                        // Check for other conflicts (teacher, section, room)
                        if (!checkLabConflict($conn, $day, $slot['start'], $slot['end'], $subject['teacher_id'], $room['id'], $sectionId, $semester, $academicYear, $examType, $sectionDept)) {
                            // Schedule this block
                            $scheduledTimes[] = [
                                'start' => $slot['start'],
                                'end' => $slot['end'],
                                'day' => $day,
                                'duration' => $slot['duration']
                            ];
                            $scheduledBlocks++;
                            $attempts = 0; // Reset attempts counter on success

                            // If we've scheduled all required blocks, assign them
                            if ($scheduledBlocks == count($pattern['blocks'])) {
                                foreach ($scheduledTimes as $time) {
                                    assignSchedule($conn, $subject['id'], $subject['teacher_id'], $room['id'], $time['day'], $time['start'], $time['end'], $semester, $academicYear, $examType, $subject['subject_type'], $sectionId);
                                }
                                return ['scheduled' => true];
                            }
                        }
                        $attempts++;
                    }
                }
            }
        }
    }

    return [
        'scheduled' => false,
        'info' => [
            'subject_id' => $subject['id'],
            'subject_code' => $subject['subject_code'],
            'subject_name' => $subject['subject_name'],
            'section_id' => $section['id'],
            'subject_type' => $subject['subject_type'],
            'minutes_per_week' => $subject['minutes_per_week'],
            'teacher_id' => $subject['teacher_id'],
            'section_name' => $section['section_name'] ?? "Section {$section['id']}",
            'reason' => 'Could not find available lab slot'
        ]
    ];
}

function generateFlexibleLabSlots($startHour, $endHour, $lunchBreak, $duration)
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

        // Use 5-minute gaps between slots for better utilization
        $currentTime = $slotEnd + (5 * 60);
    }

    return $slots;
}



function checkLabConflict($conn, $day, $start, $end, $teacherId, $roomId, $sectionId, $semester, $academicYear, $examType, $sectionDept)
{
    // First check teacher and section conflicts
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
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return true;
    }

    // Check room conflicts - no longer allowing multiple labs in same room at same time
    $stmt = $conn->prepare("
        SELECT 1 FROM schedules 
        WHERE day = ? AND exam_type = ?
        AND (
            (start_time < ? AND end_time > ?) OR
            (start_time < ? AND end_time > ?) OR
            (start_time >= ? AND end_time <= ?)
        )
        AND classroom_id = ?
        AND subject_type = 'lab'
        AND semester = ? AND academic_year = ?
    ");
    $stmt->bind_param(
        'ssssssssiss',
        $day,
        $examType,
        $end,
        $start,
        $start,
        $end,
        $start,
        $end,
        $roomId,
        $semester,
        $academicYear
    );
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}



function scheduleSubjectWithFallback($conn, $subject, $section, $days, $schoolDayStart, $schoolDayEnd, $btvtedDayStart, $btvtedDayEnd, $lunchBreak, $semester, $academicYear, $examType, $attempt)
{
    $sectionId = $section['id'];
    $sectionDept = $section['department'];

    // Allow more flexible room assignments on retry attempts
    $roomTypes = [$subject['subject_type']];
    if ($attempt > 0) {
        if ($subject['subject_type'] === 'lab') {
            $roomTypes[] = 'lecture';
        }
    }

    $rooms = [];
    foreach ($roomTypes as $type) {
        $rooms = array_merge($rooms, getClassrooms($conn, $type, $sectionDept, $attempt > 0, $subject['department']));
    }

    if (empty($rooms)) {
        return [
            'scheduled' => false,
            'info' => [
                'subject_id' => $subject['id'],
                'subject_code' => $subject['subject_code'],
                'subject_name' => $subject['subject_name'],
                'section_id' => $section['id'],
                'subject_type' => $subject['subject_type'],
                'minutes_per_week' => $subject['minutes_per_week'],
                'teacher_id' => $subject['teacher_id'],
                'section_name' => $section['section_name'] ?? "Section {$section['id']}",
                'reason' => 'No classroom available'
            ]
        ];
    }

    // Determine duration and required slots
    if ($subject['subject_type'] === 'lecture') {
        // Allow more splitting on retry attempts
        $maxDuration = $attempt > 0 ? 90 : 180;
        list($duration, $requiredSlots) = calculateOptimalDuration($subject['minutes_per_week'], $maxDuration);
    } elseif ($subject['subject_type'] === 'pe') {
        $duration = 120;
        $requiredSlots = 1;
    } else { // lab
        $duration = $subject['minutes_per_week'];
        $requiredSlots = 1;
    }

    $scheduledSlots = 0;

    // Try multiple strategies for better scheduling
    $strategies = [
        'sequential_days' => true,
        'distributed' => true,
        'evening' => $attempt > 0
    ];

    foreach ($strategies as $strategy => $enabled) {
        if (!$enabled || $scheduledSlots >= $requiredSlots) continue;

        $dayOrder = $days;
        if ($strategy === 'sequential_days') {
            shuffle($dayOrder);
        }

        foreach ($dayOrder as $day) {
            if ($scheduledSlots >= $requiredSlots) break;

            // Determine day schedule - extend hours on retry attempts
            $isDepartmentSpecial = in_array($sectionDept, ['BTVTED_All', 'BTVTED_Garments', 'BTVTED_Electronics', 'BTVTED_Electrical']);
            $dayStart = $isDepartmentSpecial ? $btvtedDayStart : $schoolDayStart;
            $dayEnd = $isDepartmentSpecial ? $btvtedDayEnd : $schoolDayEnd;

            if ($attempt > 0) {
                $dayStart = '07:00 AM';
                $dayEnd = '06:00 PM';
            }

            $daySlots = generateFlexibleSlots($dayStart, $dayEnd, $lunchBreak, $duration);

            // Try each room
            foreach ($rooms as $room) {
                if ($scheduledSlots >= $requiredSlots) break;

                // Try each time slot
                foreach ($daySlots as $slot) {
                    [$start, $end] = $slot;

                    // Check for conflicts
                    if (
                        !checkConflict($conn, $day, $start, $end, $subject['teacher_id'], $room['id'], $sectionId, $semester, $academicYear, $examType, $subject['subject_type'], $subject['department'], $subject['id']) &&
                        !checkSubjectOnDay($conn, $subject['id'], $day, $sectionId, $semester, $academicYear, $examType)
                    ) {
                        assignSchedule($conn, $subject['id'], $subject['teacher_id'], $room['id'], $day, $start, $end, $semester, $academicYear, $examType, $subject['subject_type'], $sectionId);
                        $scheduledSlots++;

                        if ($scheduledSlots >= $requiredSlots) {
                            return ['scheduled' => true];
                        }
                    }
                }
            }
        }
    }

    return [
        'scheduled' => false,
        'info' => [
            'subject_id' => $subject['id'],
            'subject_code' => $subject['subject_code'],
            'subject_name' => $subject['subject_name'],
            'section_id' => $section['id'],
            'subject_type' => $subject['subject_type'],
            'minutes_per_week' => $subject['minutes_per_week'],
            'teacher_id' => $subject['teacher_id'],
            'section_name' => $section['section_name'] ?? "Section {$section['id']}",
            'reason' => 'Could not find available time slot'
        ]
    ];
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

    $sections = getSections($conn, $semester, $academicYear);

    if (empty($sections)) {
        header("Location: ../views/admin/admin-scheduler.php?error=No sections found.");
        exit();
    }

    if ($examType === 'none') {
        // === UPDATED CONFIGURATION ===
        $schoolDayStart = '07:00 AM';
        $schoolDayEnd = '06:00 PM';
        $btvtedDayStart = '07:00 AM';
        $btvtedDayEnd = '06:00 PM';
        // Updated lunch break timing: 12:20 PM - 1:20 PM
        $lunchBreak = ['12:20 PM', '01:20 PM'];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        // Sort sections by priority (higher years first, then by department complexity)
        usort($sections, function ($a, $b) {
            if ($a['year_level'] !== $b['year_level']) {
                return $b['year_level'] <=> $a['year_level'];
            }

            // Prioritize departments with more constraints
            $deptPriority = [
                'BTVTED_All' => 1,
                'BTVTED_Garments' => 2,
                'BTVTED_Electronics' => 3,
                'BTVTED_Electrical' => 4,
                'BSIT' => 5,
                'BSED_All' => 6,
                'BSED_Mathematics' => 7,
                'BSED_Social_Studies' => 8,
                'BAT_Crops_Production' => 9,
                'BSA_Agronomy' => 10
            ];

            $aPriority = $deptPriority[$a['department']] ?? 99;
            $bPriority = $deptPriority[$b['department']] ?? 99;

            return $aPriority <=> $bPriority;
        });

        // Use improved multi-pass scheduling
        $report = scheduleSubjectsMultiPass($conn, $sections, $semester, $academicYear, $examType, $days, $schoolDayStart, $schoolDayEnd, $btvtedDayStart, $btvtedDayEnd, $lunchBreak);

        // Store report
        session_start();
        $_SESSION['scheduling_report'] = $report;

        if ($report['subjects_not_scheduled_count'] > 0) {
            header("Location: ../views/admin/admin-scheduler.php?msg=Schedules generated with " . $report['subjects_not_scheduled_count'] . " unscheduled subjects.");
        } else {
            header("Location: ../views/admin/admin-scheduler.php?msg=All schedules generated successfully with zero unscheduled subjects!");
        }
        exit();
    } else {
        // === AGGRESSIVE EXAM SCHEDULING CONFIGURATION ===
        $examDuration = 90; // Fixed 90 minutes for all exams
        $examDays = ['Wednesday', 'Thursday', 'Friday'];
        $examSlots = [
            ['07:00:00', '08:30:00'],
            ['08:40:00', '10:10:00'],
            ['10:20:00', '11:50:00'],
            ['13:00:00', '14:30:00'],
            ['14:40:00', '16:10:00'],
            ['16:20:00', '17:50:00']
        ];

        $subjectsWithoutTeachers = [];
        $subjectsNotScheduled = [];
        $sectionsScheduled = [];

        // Sort sections by year level (higher years first)
        usort($sections, function ($a, $b) {
            return $b['year_level'] <=> $a['year_level'];
        });

        foreach ($sections as $section) {
            $sectionId = $section['id'];
            $subjects = getSubjectsForSection($conn, $sectionId, $section['semester'], $academicYear);

            // Filter to only include lecture and PE subjects
            $examSubjects = array_filter($subjects, function ($subject) {
                return $subject['subject_type'] === 'lecture' || $subject['subject_type'] === 'pe';
            });

            // Sort PE subjects first since they need special rooms
            usort($examSubjects, function ($a, $b) {
                if ($a['subject_type'] === 'pe' && $b['subject_type'] !== 'pe') return -1;
                if ($b['subject_type'] === 'pe' && $a['subject_type'] !== 'pe') return 1;
                return 0;
            });

            foreach ($examSubjects as $subject) {
                if (empty($subject['teacher_ids'])) {
                    $subjectsWithoutTeachers[] = [
                        'subject_code' => $subject['subject_code'],
                        'subject_name' => $subject['subject_name'],
                        'section_id' => $sectionId,
                        'section_name' => $section['section_name'] ?? "Section {$sectionId}"
                    ];
                    continue;
                }

                $scheduled = false;
                $attempts = 0;
                $maxAttempts = 4; // Increased number of attempts

                while (!$scheduled && $attempts < $maxAttempts) {
                    switch ($attempts) {
                        case 0: // First try: normal order, department-specific rooms
                            $daysToTry = $examDays;
                            $slotsToTry = $examSlots;
                            $allowFallback = false;
                            break;
                        case 1: // Second try: reverse order, allow fallback rooms
                            $daysToTry = array_reverse($examDays);
                            $slotsToTry = array_reverse($examSlots);
                            $allowFallback = true;
                            break;
                        case 2: // Third try: shuffled order, any room
                            $daysToTry = $examDays;
                            shuffle($daysToTry);
                            $slotsToTry = $examSlots;
                            shuffle($slotsToTry);
                            $allowFallback = true;
                            break;
                        case 3: // Fourth try: extreme mode - try all possible combinations
                            $daysToTry = $examDays;
                            $slotsToTry = $examSlots;
                            $allowFallback = true;
                            // Get all possible rooms including from other departments
                            $allRooms = getClassrooms($conn, $subject['subject_type'], null, true);
                            break;
                    }

                    // Try each available teacher for this subject
                    foreach ($subject['teacher_ids'] as $teacherId) {
                        if ($scheduled) break;

                        // Try each exam day
                        foreach ($daysToTry as $day) {
                            if ($scheduled) break;

                            // Try each time slot
                            foreach ($slotsToTry as $slot) {
                                [$start, $end] = $slot;

                                // Get appropriate classrooms
                                $rooms = ($attempts === 3) ? $allRooms :
                                    getClassrooms($conn, $subject['subject_type'], $section['department'], $allowFallback);

                                foreach ($rooms as $room) {
                                    if (!checkConflict($conn, $day, $start, $end, $teacherId, $room['id'], $sectionId, $semester, $academicYear, $examType, $subject['subject_type'], $subject['department'], $subject['id'])) {

                                        assignSchedule($conn, $subject['id'], $teacherId, $room['id'], $day, $start, $end, $semester, $academicYear, $examType, $subject['subject_type'], $sectionId);
                                        $scheduled = true;
                                        break 4; // Break out of all loops
                                    }
                                }
                            }
                        }
                    }

                    $attempts++;
                }

                if (!$scheduled) {
                    // Final desperate attempt - try adjacent time slots
                    foreach ($examDays as $day) {
                        if ($scheduled) break;

                        // Generate all possible 90-minute slots within exam hours
                        $startTimes = [];
                        $current = strtotime('07:00:00');
                        $end = strtotime('18:00:00');

                        while ($current + ($examDuration * 60) <= $end) {
                            $startTimes[] = date('H:i:s', $current);
                            $current += 10 * 60; // Move in 10-minute increments
                        }

                        // Try each available teacher for this subject
                        foreach ($subject['teacher_ids'] as $teacherId) {
                            if ($scheduled) break;

                            foreach ($startTimes as $startTime) {
                                $endTime = date('H:i:s', strtotime($startTime) + ($examDuration * 60));

                                // Skip if this overlaps with existing exam slots
                                $overlaps = false;
                                foreach ($examSlots as $slot) {
                                    if ((strtotime($startTime) < strtotime($slot[1]) &&
                                        (strtotime($endTime) > strtotime($slot[0])))) {
                                        $overlaps = true;
                                        break;
                                    }
                                }
                                if ($overlaps) continue;

                                $rooms = getClassrooms($conn, $subject['subject_type'], null, true);
                                foreach ($rooms as $room) {
                                    if (!checkConflict($conn, $day, $startTime, $endTime, $teacherId, $room['id'], $sectionId, $semester, $academicYear, $examType, $subject['subject_type'], $subject['department'])) {
                                        assignSchedule($conn, $subject['id'], $teacherId, $room['id'], $day, $startTime, $endTime, $semester, $academicYear, $examType, $subject['subject_type'], $sectionId);
                                        $scheduled = true;
                                        break 4;
                                    }
                                }
                            }
                        }
                    }

                    if (!$scheduled) {
                        $subjectsNotScheduled[] = [
                            'subject_id' => $subject['id'],
                            'subject_code' => $subject['subject_code'],
                            'subject_name' => $subject['subject_name'],
                            'section_id' => $section['id'],
                            'subject_type' => $subject['subject_type'],
                            'teacher_id' => null, // Couldn't schedule with any teacher
                            'section_name' => $section['section_name'] ?? "Section {$section['id']}",
                            'reason' => 'No available slot after exhaustive search'
                        ];
                    }
                }
            }

            $sectionsScheduled[] = $sectionId;
        }

        // Store report
        session_start();
        $_SESSION['scheduling_report'] = [
            'sections_scheduled' => count($sectionsScheduled),
            'subjects_without_teachers' => $subjectsWithoutTeachers,
            'subjects_not_scheduled' => $subjectsNotScheduled,
            'subjects_not_scheduled_count' => count($subjectsNotScheduled)
        ];

        if (count($subjectsNotScheduled) > 0) {
            header("Location: ../views/admin/admin-scheduler.php?msg=Exam schedules generated with " . count($subjectsNotScheduled) . " unscheduled subjects.");
        } else {
            header("Location: ../views/admin/admin-scheduler.php?msg=All exam schedules generated successfully!");
        }
        exit();
    }
}
