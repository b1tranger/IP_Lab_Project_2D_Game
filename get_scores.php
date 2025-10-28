<?php
// --- get_scores.php (Example for a traditional server) ---
// This file would fetch the scores for a user.

header('Content-Type: application/json');

// --- Database Connection ---
$servername = "localhost";
$username = "your_db_user";
$password = "your_db_password";
$dbname = "game_scores";

// Get user ID from query parameter
$userId = $_GET['userId'] ?? 'unknown';

if ($userId === 'unknown') {
    http_response_code(400);
    echo json_encode(['message' => 'User ID required.']);
    exit();
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// --- Select scores ---
// Use prepared statements
$stmt = $conn->prepare("SELECT score, won, createdAt FROM scores WHERE user_id = ? ORDER BY score DESC LIMIT 10");
$stmt->bind_param("s", $userId);

$stmt->execute();
$result = $stmt->get_result();

$scores = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $scores[] = $row;
    }
}

echo json_encode($scores);

$stmt->close();
$conn->close();

?>
