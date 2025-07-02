<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../models/functions.php';

// Get query parameters
$semester = $_GET['semester'];
$type = $_GET['type'];
$classroomId = $_GET['classroom'];
$academicYear = $_GET['academic_year'];

// Fetch schedule data for the selected classroom, semester, and academic year
$stmt = $conn->prepare("
    SELECT 
        schedules.*, 
        subjects.subject_name, 
        subjects.subject_code, 
        users.name AS teacher_name,
        sections.section_name,
        CASE 
            WHEN schedules.subject_type = 'online' THEN 'Online'
            ELSE classrooms.room_number 
        END AS room_number,
        subjects.subject_type
    FROM schedules
    INNER JOIN subjects ON schedules.subject_id = subjects.id
    INNER JOIN users ON schedules.teacher_id = users.id
    INNER JOIN sections ON schedules.section_id = sections.id
    LEFT JOIN classrooms ON schedules.classroom_id = classrooms.id
    WHERE schedules.classroom_id = ? 
    AND schedules.semester = ? 
    AND schedules.academic_year = ? 
    AND schedules.exam_type = ?
    ORDER BY 
        FIELD(schedules.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
        schedules.start_time,
        sections.section_name
");
$stmt->bind_param('isss', $classroomId, $semester, $academicYear, $type);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    if ($type === 'none') {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => 'No class schedules found for the selected classroom, semester, and academic year'
        ]);
        exit;
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => 'No exam schedules found for the selected classroom, semester, and academic year'
        ]);
        exit;
    }
}

// Store the fetched data in an array
$scheduleData = [];
while ($row = $result->fetch_assoc()) {
    $scheduleData[] = $row;
}

// Close the database connection
$conn->close();

// Define days
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

// Function to convert time to 12-hour format
function formatTime($time)
{
    return date('h:i A', strtotime($time));
}

// Generate time slots dynamically based on the schedule data
$timeSlots = [];
foreach ($scheduleData as $schedule) {
    $startTime = $schedule['start_time'];
    $endTime = $schedule['end_time'];
    $timeSlot = formatTime($startTime) . ' - ' . formatTime($endTime);
    if (!in_array($timeSlot, $timeSlots)) {
        $timeSlots[] = $timeSlot;
    }
}

// Add lunch break time slot
$lunchBreakSlot = '12:00 PM - 01:00 PM';
if (!in_array($lunchBreakSlot, $timeSlots)) {
    $timeSlots[] = $lunchBreakSlot;
}

// Sort time slots in ascending order
usort($timeSlots, function ($a, $b) {
    return strtotime(explode(' - ', $a)[0]) - strtotime(explode(' - ', $b)[0]);
});

// Initialize the response array
$response = [];

// First, group all schedules by their time slot and day
$groupedSchedules = [];
foreach ($scheduleData as $schedule) {
    $timeSlot = formatTime($schedule['start_time']) . ' - ' . formatTime($schedule['end_time']);
    $day = strtolower($schedule['day']);
    $key = $timeSlot . '_' . $day;

    if (!isset($groupedSchedules[$key])) {
        $groupedSchedules[$key] = [];
    }

    $groupedSchedules[$key][] = $schedule;
}

// Now build the response array
foreach ($timeSlots as $timeSlot) {
    $row = ['time' => $timeSlot];

    // Initialize each day's column
    foreach ($days as $day) {
        $row[strtolower($day)] = '';
    }

    // Check if there are schedules for each day at this time slot
    foreach ($days as $day) {
        $key = $timeSlot . '_' . strtolower($day);

        if (isset($groupedSchedules[$key])) {
            $schedules = $groupedSchedules[$key];
            $entries = [];

            // Group identical schedules (same subject, same teacher) together
            $groupedEntries = [];
            foreach ($schedules as $schedule) {
                $entryKey = $schedule['subject_code'] . '_' . $schedule['teacher_name'] . '_' . $schedule['subject_type'];

                if (!isset($groupedEntries[$entryKey])) {
                    $groupedEntries[$entryKey] = [
                        'subject_code' => $schedule['subject_code'],
                        'teacher_name' => $schedule['teacher_name'],
                        'subject_type' => $schedule['subject_type'],
                        'sections' => []
                    ];
                }

                $groupedEntries[$entryKey]['sections'][] = $schedule['section_name'];
            }

            // Build the display entries
            foreach ($groupedEntries as $entry) {
                $subjectDisplay = $entry['subject_code'];

                $sectionsDisplay = implode(', ', $entry['sections']);
                $teacherDisplay = $entry['teacher_name'];

                $entryDisplay = $subjectDisplay . '<br>' .
                    $sectionsDisplay . '<br>' .
                    $teacherDisplay;

                // Add subject type indicator if not lecture
                if ($entry['subject_type'] === 'lab') {
                    $entryDisplay .= ' (Lab)';
                } else if ($entry['subject_type'] === 'pe') {
                    $entryDisplay .= ' (PE)';
                } else if ($entry['subject_type'] === 'online') {
                    $entryDisplay .= ' (Online)';
                }

                $entries[] = $entryDisplay;
            }

            // Combine all entries for this time slot/day with a separator
            $row[strtolower($day)] = implode('<hr style="margin: 5px 0; border-top: 1px dashed #ccc;">', $entries);
        }
    }

    // Mark lunch break
    if ($timeSlot === $lunchBreakSlot) {
        foreach ($days as $day) {
            $row[strtolower($day)] = 'Lunch Break';
        }
    }

    $response[] = $row;
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

//395 	94 	31 	100 	Monday 	07:00:00 	08:00:00 	1 	2025-2026 	none 	lecture