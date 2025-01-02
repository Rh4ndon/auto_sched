<?php
include 'dbconn.php';


function insertRecord($table, $data)
{
    global $conn;
    $columns = implode(", ", array_keys($data));
    $values  = implode("', '", array_values($data));
    $query = "INSERT INTO $table ($columns) VALUES ('$values')";
    return mysqli_query($conn, $query);
}

function editRecord($table, $data, $condition)
{
    global $conn;
    $updateData = [];
    foreach ($data as $column => $value) {
        $updateData[] = "$column = '$value'";
    }
    $updateString = implode(", ", $updateData);
    $query = "UPDATE $table SET $updateString WHERE $condition";
    return mysqli_query($conn, $query);
}

function deleteRecord($table, $condition)
{
    global $conn;
    $query = "DELETE FROM $table WHERE $condition";
    return mysqli_query($conn, $query);
}

function getAllRecords($table, $condition = '')
{
    global $conn;
    $query = "SELECT * FROM $table $condition";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getRecord($table, $condition)
{
    global $conn;
    $query = "SELECT * FROM $table WHERE $condition";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function getRecordMultiTable($table1, $table2, $onCondition, $whereCondition)
{
    global $conn;
    $query = "SELECT * FROM $table1 LEFT JOIN $table2 ON $onCondition WHERE $whereCondition";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function getLastAttendanceRecord($student_id)
{
    global $conn;
    // Ensure we fetch the latest record by ordering by date and time
    $query = "SELECT * FROM attendance WHERE student_id = $student_id ORDER BY date DESC, time DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}


function isTimeIn($student_id, $date)
{
    global $conn;
    $query = "SELECT * FROM attendance WHERE student_id = $student_id AND date = '$date' ORDER BY time DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    $last_record = mysqli_fetch_assoc($result);
    return $last_record && $last_record['remark'] === 'time-in';
}

function countAllRecords($table, $whereCondition = '1')
{
    global $conn;
    $query = "SELECT COUNT(*) as total FROM $table WHERE $whereCondition";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function getAttendanceData()
{
    global $conn;
    $query = "SELECT date, COUNT(*) as total FROM attendance GROUP BY date ORDER BY date ASC";
    $result = mysqli_query($conn, $query);
    $dates = [];
    $values = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $dates[] = $row['date'];
        $values[] = $row['total'];
    }
    return ['dates' => $dates, 'values' => $values];
}
