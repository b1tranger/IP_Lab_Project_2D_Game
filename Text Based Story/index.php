<?php

session_start();



include("connection.php");
if (isset($_POST["submit"])) {
    $username = $_POST['username'];
    $_SESSION['username'] = $username;
    $sql = "INSERT INTO progression (username,game_progress) VALUES('$username','You started your journey... ')";
    mysqli_query($conn, $sql);
    $sql2 = "SELECT * FROM progression WHERE username='$username'";
    // may conflict with duplicate names
    $result = mysqli_query($conn, $sql2);
    $row = mysqli_fetch_array($result);
    $user_id = $row['user_id'];
    $_SESSION['user_id'] = $user_id;
    $row_check = mysqli_num_rows($result);

    $RNG = random_int(1, 10);
    $sqlChoice = "SELECT * FROM story WHERE story_id='$RNG'";
    $sqlChoiceQuery = mysqli_query($conn, $sqlChoice);
    $sqlChoiceFetch = mysqli_fetch_assoc($sqlChoiceQuery);
    $Choice1 = $sqlChoiceFetch["choice1"];
    $Choice2 = $sqlChoiceFetch["choice2"];
    $StoryUpdate = $sqlChoiceFetch['story'];
    $updateStory = "UPDATE progression SET game_progress = CONCAT(game_progress, '$StoryUpdate') WHERE user_id = '$user_id'";
    mysqli_query($conn, $updateStory);
    $_SESSION['story_state'] = $RNG;
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
            <hr style="max-width:300px;">
            <form method="POST" style="padding-top: 100px;">
                <label for="username">Enter Username:</label>
                <br><br>
                <input type="text" name="username">
                <br><br>
                <input type="submit" name="submit" value="Start Game">
            </form>
            <div style="max-width:300px; margin:auto;border:1px solid; border-radius:15px; margin-top:100px;">
                <?php
                if (isset($_POST["submit"])) {

                    if ($row_check > 0) {
                        echo "<hr>";
                        echo "<p style='color:green;'>User registered. Redirecting to game in 3 seconds...</p>";
                        echo '<meta http-equiv="refresh" content="3;url=oopGame.php">';
                        echo "<hr>";
                    } else {
                        echo "<hr>";
                        echo "<p style='color:red;'>User not found. Please try again.</p>";
                        echo "<hr>";
                    }
                }
                ?>
            </div>

    </div>
</body>

</html>