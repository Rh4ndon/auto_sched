<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../models/functions.php';

// Get query parameters
$semester = $_GET['semester'];
$type = $_GET['type'];
$academicYear = $_GET['academic_year'];
$teacherId = $_GET['teacher_id'];

// Fetch schedule data for the selected section, semester, and academic year
$stmt = $conn->prepare("
    SELECT schedules.*, subjects.subject_name, subjects.subject_code, users.name AS teacher_name, classrooms.room_number,classrooms.room_name,classrooms.department, subjects.subject_type, sections.section_name
    FROM schedules
    INNER JOIN subjects ON schedules.subject_id = subjects.id
    INNER JOIN users ON schedules.teacher_id = users.id
    INNER JOIN classrooms ON schedules.classroom_id = classrooms.id
    INNER JOIN sections ON schedules.section_id = sections.id
    WHERE schedules.teacher_id = ? AND schedules.semester = ? AND schedules.academic_year = ? AND schedules.exam_type = ?
    ORDER BY schedules.day, schedules.start_time
");
$stmt->bind_param('iiss', $teacherId, $semester, $academicYear, $type);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows === 0) {

    if ($type === 'none') {
        // Set the HTTP response code to 404 Not Found
        http_response_code(404);
        // Set the content type to JSON
        header('Content-Type: application/json');

        // Return a JSON response with an error message
        echo json_encode([
            'error' => true,
            'message' => 'No class schedules found for the selected section, semester, and academic year'
        ]);
        // Exit the script to prevent further execution
        exit;
    } else {
        // Set the HTTP response code to 404 Not Found
        http_response_code(404);
        // Set the content type to JSON
        header('Content-Type: application/json');

        // Return a JSON response with an error message
        echo json_encode([
            'error' => true,
            'message' => 'No exam schedules found for the selected section, semester, and academic year'
        ]);
        // Exit the script to prevent further execution
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

// Populate the response array with schedule data
foreach ($timeSlots as $timeSlot) {
    $row = ['time' => $timeSlot];

    // Initialize each day's column
    foreach ($days as $day) {
        $row[strtolower($day)] = '';
    }

    // Fill in the schedule data
    foreach ($scheduleData as $schedule) {
        $scheduleTimeSlot = formatTime($schedule['start_time']) . ' - ' . formatTime($schedule['end_time']);
        if ($scheduleTimeSlot === $timeSlot) {
            $day = strtolower($schedule['day']);
            $subjectInfo = $schedule['subject_code'] . ' <br> ' . $schedule['teacher_name'] . ' <br> ' . $schedule['section_name'];
            if ($schedule['subject_type'] === 'lecture') {
                $row[$day] = $subjectInfo . '<br>' . $schedule['department']  . ', ' . $schedule['room_name']  . ' Room (' . $schedule['room_number'] . ')';
            } else if ($schedule['subject_type'] === 'lab') {
                $row[$day] = $subjectInfo . '<br>' . $schedule['department']  . ', ' . $schedule['room_name']  . ' Lab (' . $schedule['room_number'] . ')';
            } else if ($schedule['subject_type'] === 'pe') {
                $row[$day] = $subjectInfo . '<br>' . $schedule['department']  . ', ' . $schedule['room_name']  . ' Gym (' . $schedule['room_number'] . ')';
            }
        }
    }



    // Mark lunch break
    if ($timeSlot === $lunchBreakSlot) {
        foreach ($days as $day) {
            $row[strtolower($day)] = 'Lunch Break';
        }
    }

    // Mark Friday as Online Class
    /*
    if ($type === 'none') {
        $row['friday'] = 'Online Class';
    }
*/

    $response[] = $row;
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
