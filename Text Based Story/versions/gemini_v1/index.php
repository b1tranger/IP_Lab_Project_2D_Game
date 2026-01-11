<?php
session_start();
include("connection.php");

// Clear old game session data on new start,
// but keep user login info if you had it.
unset($_SESSION['story_state']);
unset($_SESSION['visited_stories']);
unset($_SESSION['total_score']);

if (isset($_POST["submit"])) {
    $username = $_POST['username'];
    $_SESSION['username'] = $username;

    // --- USE PREPARED STATEMENTS TO PREVENT SQL INJECTION ---
    $sql_check = "SELECT * FROM progression WHERE username = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    $user_id = 0;

    if ($result_check->num_rows > 0) {
        // --- USER EXISTS ---
        // Log them in, get their ID, and RESET their score for a new game
        $row = $result_check->fetch_assoc();
        $user_id = $row["user_id"];
        $_SESSION["user_id"] = $user_id;

        // Reset score and progress for new game
        $sql_reset = "UPDATE progression SET score_sum = 0, game_progress = 'You started your journey. ' WHERE user_id = ?";
        $stmt_reset = $conn->prepare($sql_reset);
        $stmt_reset->bind_param("i", $user_id);
        $stmt_reset->execute();

        $_SESSION['total_score'] = 0; // Session score to 0

    } else {
        // --- NEW USER ---
        // Insert them into the database with a score of 0
        $sql_insert = "INSERT INTO progression (username, game_progress, score_sum) VALUES(?, 'You started your journey. ', 0)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("s", $username);
        $stmt_insert->execute();

        $user_id = $conn->insert_id; // Get the new user_id
        $_SESSION["user_id"] = $user_id;
        $_SESSION['total_score'] = 0; // Session score to 0
    }

    // --- START THE GAME ---

    // 1. Initialize the list of visited stories (it's empty for now)
    $_SESSION['visited_stories'] = [];

    // 2. Get the first random story (1-10)
    $RNG = random_int(1, 10);

    // 3. Add this first story to the visited list
    $_SESSION['visited_stories'][] = $RNG;

    // 4. Set this as the current story state
    $_SESSION['story_state'] = $RNG;

    // 5. Get the first story text and add it to the user's progress
    $sql_first_story = "SELECT story FROM story WHERE story_id = ?";
    $stmt_story = $conn->prepare($sql_first_story);
    $stmt_story->bind_param("i", $RNG);
    $stmt_story->execute();
    $story_row = $stmt_story->get_result()->fetch_assoc();
    $StoryUpdate = $story_row['story'];

    $updateStory = "UPDATE progression SET game_progress = CONCAT(game_progress, ?, ' ') WHERE user_id = ?";
    $stmt_update = $conn->prepare($updateStory);
    $stmt_update->bind_param("si", $StoryUpdate, $user_id);
    $stmt_update->execute();

    // We will now redirect to the game page
    $row_check = 1; // Set this to pass the check below
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .link {
            color: #87ceeb;
            text-decoration: none;
        }

        .link:hover {
            transition: 0.3s ease;
            transform: translateY(-3px);
            color: black;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <!-- Reverted body layout to original -->
    <div style="padding-top:10vh;margin:auto;text-align:center;">
        <h1 style="text-align:center">IP Lab Project: 2D Story Game</h2>
            <h2 style="text-align:center; font-size: 1rem; color: #555;"><a
                    href="https://github.com/b1tranger/IP_Lab_Project_2D_Game" target="_blank"
                    class="link">0432410005101088</a></h2>
            <hr style="max-width:300px;">
            <form method="POST" style="padding-top: 50px;">
                <label for="username">Enter Username:</label>
                <br><br>
                <input type="text" name="username" required>
                <br><br>
                <input type="submit" name="submit" value="Start Game">
            </form>
            <div
                style="max-width:300px; margin:auto;border:1px solid; border-radius:15px; margin-top:50px; padding: 10px;">
                <?php
                if (isset($_POST["submit"])) {
                    if ($row_check > 0) {
                        echo "<p style='color:green;'>User ready. Redirecting to game in 3 seconds...</p>";
                        echo '<meta http-equiv="refresh" content="3;url=oopGame.php">';
                    } else {
                        echo "<p style='color:red;'>User not found or creation failed. Please try again.</p>";
                    }
                }
                ?>
            </div>
    </div>
</body>

</html>