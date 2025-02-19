<?php
session_start();
header('Content-Type: application/json');

// Check if session is set
if (!isset($_SESSION['id'])) {
    echo json_encode(["error" => "No active session"]);
    exit;
}

// Return session data as JSON
echo json_encode([
    "id" => $_SESSION['id'],
    "user_type" => $_SESSION['user_type'] ?? "guest"  // Ensure user_type is always set
]);
exit;
