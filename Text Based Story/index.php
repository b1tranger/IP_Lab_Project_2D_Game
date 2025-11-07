<?php

session_start();
include("connection.php");

// --- USE PREPARED STATEMENTS TO PREVENT SQL INJECTION ---
if (isset($_POST["submit"])) {
    $username = $_POST['username'];
    $_SESSION['username'] = $username;

    // 1. Check if user exists
    $sql_check = "SELECT * FROM progression WHERE username = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $username); // "s" means string
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    $user_id = 0;

    if ($result_check->num_rows > 0) {
        // --- USER EXISTS ---
        $row = $result_check->fetch_assoc();
        $user_id = $row['user_id'];
        $_SESSION["user_id"] = $user_id;

        // Reset score and progress for new game. Added \n\n for line breaks.
        $sql_reset = "UPDATE progression SET score_sum = 0, game_progress = 'You started your journey. \n\n' WHERE user_id = ?";
        $stmt_reset = $conn->prepare($sql_reset);
        $stmt_reset->bind_param("i", $user_id); // "i" means integer
        $stmt_reset->execute();

    } else {
        // --- NEW USER ---
        // Insert them with score 0. Added \n\n for line breaks.
        $sql_insert = "INSERT INTO progression (username, game_progress, score_sum) VALUES(?, 'You started your journey. \n\n', 0)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("s", $username);
        $stmt_insert->execute();

        $user_id = $conn->insert_id; // Get the new user_id
        $_SESSION["user_id"] = $user_id;
    }

    // --- SESSION & GAME SETUP ---
    $_SESSION['total_score'] = 0;
    $_SESSION['visited_stories'] = [];
    $RNG = random_int(1, 10);
    $_SESSION['visited_stories'][] = $RNG;
    $_SESSION['story_state'] = $RNG;

    // 6. Get the first story text
    $sql_first_story = "SELECT story FROM story WHERE story_id = ?";
    $stmt_story = $conn->prepare($sql_first_story);
    $stmt_story->bind_param("i", $RNG);
    $stmt_story->execute();
    $story_row = $stmt_story->get_result()->fetch_assoc();
    $StoryUpdate = $story_row['story'];

    // 7. Update the database with the first story line. Added \n\n for line breaks.
    $updateStory = "UPDATE progression SET game_progress = CONCAT(game_progress, ?, ' \n\n') WHERE user_id = ?";
    $stmt_update = $conn->prepare($updateStory);
    $stmt_update->bind_param("si", $StoryUpdate, $user_id); // "s" string, "i" integer
    $stmt_update->execute();

    // Set 'row_check' to pass the redirect logic
    $row_check = 1;
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
    <div style="padding-top:10vh;margin:auto;text-align:center;">
        <h1 style="text-align:center">IP Lab Project: 2D Story Game</h2>
            <h2 style="text-align:center"><a href="https://github.com/b1tranger/IP_Lab_Project_2D_Game" target="_blank"
                    class="link">0432410005101088</a></h2>
            <hr style="max-width:300px;margin-bottom:50px;">
            <img src="assets/Warrior-idle.gif" height="200px" style="margin-top: -30px;margin-bottom: -30px;">
            <form method="POST" style="padding-top: 20px;">
                <label for="username">Enter Username:</label>
                <br><br>
                <input type="text" name="username" required>
                <br><br>
                <input type="submit" name="submit" value="Start Game" style="cursor: pointer;">
            </form>
            <div style="max-width:300px; margin:auto;border:1px solid; border-radius:15px; margin-top:100px;">
                <?php
                if (isset($_POST["submit"])) {
                    if (isset($row_check) && $row_check > 0) {
                        echo "<hr>";
                        echo "<p style='color:green;'>User ready. Redirecting to game in 3 seconds...</p>";
                        echo '<meta http-equiv="refresh" content="3;url=oopGame.php">';
                        echo "<hr>";
                    } else {
                        echo "<hr>";
                        echo "<p style='color:red;'>User not found or creation failed. Please try again.</p>";
                        echo "<hr>";
                    }
                }
                ?>
            </div>
    </div>
</body>

</html>