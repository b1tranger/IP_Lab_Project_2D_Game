<?php

include("connection.php");
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$current_story_id = $_SESSION['story_state'];
$visited_stories = $_SESSION['visited_stories'] ?? [];
$total_score = $_SESSION['total_score'] ?? 0;
// This new session variable tracks if we've shown the new story yet
$awaiting_next_story = $_SESSION['awaiting_next_story'] ?? false;


if ($current_story_id == 0) {
    $sqlProgress = "SELECT game_progress, score_sum FROM progression WHERE user_id = ?";
    $stmt = $conn->prepare($sqlProgress);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $sqlProgressFetch = $stmt->get_result()->fetch_assoc();
    $final_progress = $sqlProgressFetch['game_progress'];
    $final_score = $sqlProgressFetch['score_sum'];

    echo "<div style='text-align: center; margin-top: 20vh;'>";
    echo "<h1>You Died!</h1>";
    echo "<h2>Total Score: " . $final_score . "</h2>";
    echo "<p>Your journey has ended.</p>";
    echo "<a href='index.php'>Play Again</a>";
    // Added pre-wrap and htmlspecialchars for the final screen
    echo "<hr><div style='text-align:left; max-width: 500px; margin: auto; white-space: pre-wrap;'>" . htmlspecialchars($final_progress) . "</div>";
    echo "</div>";

    session_destroy();
    exit();
}


class game
{
    public $conn;
    public $user_id;
    public $Choice1;
    public $Choice2;
    public $current_story_id;
    public $visited_stories;
    public $total_score;

    public function __construct($conn, $user_id, $story_id, $visited, $score)
    {
        $this->conn = $conn;
        $this->user_id = $user_id;
        $this->current_story_id = $story_id;
        $this->visited_stories = $visited;
        $this->total_score = $score;
    }

    public function loadCurrentChoices()
    {
        $sql = "SELECT choice1, choice2 FROM story WHERE story_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->current_story_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        $this->Choice1 = $row["choice1"];
        $this->Choice2 = $row["choice2"];
    }

    public function choice1()
    {
        $sql = "SELECT choice1, thoughts, c1_points FROM story WHERE story_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->current_story_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        $choice_text = $row["choice1"];
        $thoughts_text = $row["thoughts"];
        $points = (int) $row["c1_points"];

        $sql_update = "UPDATE progression 
                       SET game_progress = CONCAT(game_progress, ' ', ?, ' ', ?, ' \n\n'), 
                           score_sum = score_sum + ? 
                       WHERE user_id = ?";

        $stmt_update = $this->conn->prepare($sql_update);
        $stmt_update->bind_param("ssii", $choice_text, $thoughts_text, $points, $this->user_id);
        $stmt_update->execute();

        $_SESSION['total_score'] = $this->total_score + $points;
    }

    public function choice2()
    {
        $sql = "SELECT choice2, thoughts, c2_points FROM story WHERE story_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->current_story_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        $choice_text = $row["choice2"];
        $thoughts_text = $row["thoughts"];
        $points = (int) $row["c2_points"];

        $sql_update = "UPDATE progression 
                       SET game_progress = CONCAT(game_progress, ' ', ?, ' ', ?, ' \n\n'), 
                           score_sum = score_sum + ? 
                       WHERE user_id = ?";

        $stmt_update = $this->conn->prepare($sql_update);
        $stmt_update->bind_param("ssii", $choice_text, $thoughts_text, $points, $this->user_id);
        $stmt_update->execute();

        $_SESSION['total_score'] = $this->total_score + $points;
    }

    // NEW FUNCTION: Finds and presents the *next* story
    public function presentNextStory()
    {
        $all_stories = range(1, 10);
        $available_stories = array_diff($all_stories, $this->visited_stories);

        $RNG = 0; // Default to 0 (Game Over)

        if (!empty($available_stories)) {
            $random_key = array_rand($available_stories);
            $RNG = $available_stories[$random_key];
        }

        // Add the new story (even 0) to the visited list
        $_SESSION['visited_stories'][] = $RNG;

        // If the game is not over, add the new story text to the log
        if ($RNG != 0) {
            $sqlChoice = "SELECT story FROM story WHERE story_id = ?";
            $stmt = $this->conn->prepare($sqlChoice);
            $stmt->bind_param("i", $RNG);
            $stmt->execute();
            $sqlChoiceFetch = $stmt->get_result()->fetch_assoc();
            $StoryUpdate = $sqlChoiceFetch['story']; // The new story text

            // Append the *new* story text. Add \n\n for line break.
            $updateStory = "UPDATE progression SET game_progress = CONCAT(game_progress, ?, ' \n\n') WHERE user_id = ?";
            $stmt_update = $this->conn->prepare($updateStory);
            $stmt_update->bind_param("si", $StoryUpdate, $this->user_id);
            $stmt_update->execute();
        }

        // Save the new story state
        $_SESSION['story_state'] = $RNG;
        // We are no longer "awaiting"
        $_SESSION['awaiting_next_story'] = false;
    }

    public function gameLogic($choice)
    {
        if ($choice == 1) {
            $this->choice1();
        } elseif ($choice == 2) {
            $this->choice2();
        }

        // 2. Set a flag that we are waiting to show the next story
        $_SESSION['awaiting_next_story'] = true;
    }
}

$action = new game($conn, $user_id, $current_story_id, $visited_stories, $total_score);


if (isset($_POST['submit'])) {
    $choice = $_POST['choice'];
    $action->gameLogic($choice);

    // Redirect to this same page to prevent form re-submission
    header("Location: oopGame.php");
    exit();

} else {
    // Check if we just made a choice and need to show the next story
    if ($awaiting_next_story) {
        $action->presentNextStory();
        // We must reload *again* to show the new choices
        header("Location: oopGame.php");
        exit();
    }

    // Page is loading normally, just get the choices
    $action->loadCurrentChoices();
    $Choice1 = $action->Choice1;
    $Choice2 = $action->Choice2;
}

$sqlProgress = "SELECT game_progress, score_sum FROM progression WHERE user_id = ?";
$stmt = $conn->prepare($sqlProgress);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sqlProgressFetch = $stmt->get_result()->fetch_assoc();
$game_progress_text = $sqlProgressFetch['game_progress'];
$current_score_from_db = $sqlProgressFetch['score_sum'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .game_window {
            margin-top: 10vh;
            max-width: 500px;
            min-height: 40vh;
            margin: auto;
            border: 1px solid black;
            padding: 15px;
        }

        .align_center {
            text-align: center;
            display: flex;
            justify-content: center;
        }

        .border_top {
            border-top: 1px solid black;
            border-right: 1px solid black;
            border-left: 1px solid black;
            border-width: 10px;
            max-width: 300px;
            margin: auto;
            padding: 10px;
            border-radius: 10px;
        }

        .click {
            cursor: pointer;
        }

        .border_button {
            border-right: 1px solid black;
            border-left: 1px solid black;
            border-width: 5px;
            max-width: 300px;
            margin: auto;
            padding: 10px;
            border-radius: 10px;
        }

        .border_button:hover {
            transition: 0.3s ease;
            transform: translateY(-3px);
            cursor: pointer;
            background-color: white;
            box-shadow: 0 0 2px 2px black;
        }

        .game_section_01 {
            margin-top: 2vh;
        }

        .game_section_02 {
            margin-top: 10vh;
        }
    </style>
</head>

<body>


    <div class="game_window">
        <div class="game_section_01">
            <!-- <h2 class="align_center border_top">Game Visuals</h2> -->
            <div style="text-align: center; font-size: 1.2rem; font-family: sans-serif;"
                class="align_center border_top">
                <p>Welcome, <?php echo htmlspecialchars($username); ?>! | <b>Score:
                        <?php echo $current_score_from_db; ?></b>
                </p>
            </div>
            <hr>
            <div>
                <?php $RNG = $_SESSION['story_state']; ?>
                <?php if ($RNG == 2 || $RNG == 4 || $RNG == 5 || $RNG == 6): ?>
                    <img src="assets/Warrior-attack-defend.gif" height="200px" style="margin-bottom:-100px;">
                <?php else: ?>
                    <img src="assets/Warrior-running.gif" height="200px" style="margin-bottom:-100px;">
                <?php endif ?>
            </div>
        </div>
        <div class="game_section_02">
            <!-- <h2 class="align_center border_top">Story</h2> -->
            <hr>

            <div id="story-box"
                style="max-height: 200px; overflow-y: auto; border: 1px solid #eee; padding: 10px; white-space: pre-wrap;">
                <?php
                echo htmlspecialchars($game_progress_text);
                ?>
            </div>
        </div>
        <div class="game_section_02">
            <!-- <h2 class="align_center border_top">Choice Buttons</h2> -->
            <hr style="margin-top: -80px;margin-bottom: -20px;">
            <form method="POST">
                <br><br>
                <div style="padding-left:20px;">
                    <input type="radio" value="1" id="choice1" name="choice" class="click" required>
                    <label for="choice1">&nbsp;<?php echo htmlspecialchars($Choice1); ?></label>
                    <br><br>
                    <input type="radio" value="2" id="choice2" name="choice" class="click" required>
                    <label for="choice2">&nbsp;<?php echo htmlspecialchars($Choice2); ?></label>
                    <br><br>
                </div>
                <input type="submit" name="submit" class="align_center border_button" value="Confirm Choice">
            </form>
        </div>
    </div>

    <script>
        // Wait for the page content to be fully loaded
        document.addEventListener("DOMContentLoaded", function () {
            // Find the story box element by its ID
            var storyBox = document.getElementById("story-box");

            // Set its vertical scroll position to its maximum scroll height.
            // This effectively scrolls it to the bottom.
            storyBox.scrollTop = storyBox.scrollHeight;
        });
    </script>
</body>

</html>