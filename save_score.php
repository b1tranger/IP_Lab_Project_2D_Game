<?php
// --- save_score.php (Example for a traditional server) ---
// This file would receive the score data from the game.

// --- Database Connection ---
$servername = "localhost"; // Your database host
$username = "your_db_user";   // Your database username
$password = "your_db_password"; // Your database password
$dbname = "game_scores";     // Your database name

// Get data from the game (POST request)
// We must sanitize user input
$data = json_decode(file_get_contents('php://input'), true);

$userId = $data['userId'] ?? 'unknown'; // In a real app, you'd get this from a server session
$score = $data['score'] ?? 0;
$won = $data['won'] ?? false;

// --- Validate data ---
if ($score === 0 || $userId === 'unknown') {
    http_response_code(400); // Bad Request
    echo json_encode(['message' => 'Invalid data.']);
    exit();
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// --- Insert the score ---
// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO scores (user_id, score, won) VALUES (?, ?, ?)");
// 's' = string (for user_id), 'i' = integer (for score), 'i' = integer (for won boolean)
$stmt->bind_param("sii", $userId, $score, $won);

if ($stmt->execute()) {
    http_response_code(201); // Created
    echo json_encode(['message' => 'Score saved successfully!']);
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Error saving score: ' . $stmt->error]);
}

$stmt->close();
$conn->close();

?>
