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

function getRecordsJoin($table1, $table2, $table3, $onCondition, $onCondition2, $whereCondition)
{
    global $conn;
    $query = "SELECT * FROM $table1 JOIN $table2 ON $onCondition JOIN $table3 ON $onCondition2 WHERE $whereCondition";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function countAllRecords($table, $whereCondition = '')
{
    global $conn;
    $query = "SELECT COUNT(*) as total FROM $table $whereCondition";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}


// Scheduler functions
