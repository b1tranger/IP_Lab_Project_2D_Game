<?php

// --- DATABASE CONFIGURATION ---
// !! IMPORTANT: Replace these with your actual database credentials.
$db_host = 'localhost';     // Usually 'localhost'
$db_user = 'root';          // Your database username
$db_pass = '';              // Your database password
$db_name = 'game_db';       // Your database name

// --- SCRIPT ---

// Set header to return JSON
header('Content-Type: application/json');

// Function to send a JSON error response
function json_error($message) {
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

// 1. Connect to the database
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($mysqli->connect_error) {
    json_error('Database connection failed: ' . $mysqli->connect_error);
}

// 2. Get data from the POST request
// We get the raw POST data because we sent JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if data is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    json_error('Invalid JSON input.');
}

// 3. Validate and sanitize data
$score = $data['score'] ?? null;
$summary = $data['summary'] ?? null;
$ending_type = $data['ending_type'] ?? null;

if (!isset($score) || !is_numeric($score)) {
    json_error('Invalid or missing score.');
}

if (empty($summary) || !is_string($summary)) {
    json_error('Invalid or missing summary.');
}

if (empty($ending_type) || !is_string($ending_type)) {
    json_error('Invalid or missing ending type.');
}

// 4. Prepare and execute the SQL statement
// We use a prepared statement to prevent SQL injection.
$stmt = $mysqli->prepare('INSERT INTO game_scores (score, summary, ending_type) VALUES (?, ?, ?)');

if (!$stmt) {
    json_error('Failed to prepare statement: ' . $mysqli->error);
}

// Bind parameters (i = integer, s = string)
$stmt->bind_param('iss', $score, $summary, $ending_type);

// Execute the statement
if ($stmt->execute()) {
    // Success
    echo json_encode(['status' => 'success', 'message' => 'Score saved successfully.']);
} else {
    // Failure
    json_error('Failed to execute statement: ' . $stmt->error);
}

// 5. Close connections
$stmt->close();
$mysqli->close();

?>

