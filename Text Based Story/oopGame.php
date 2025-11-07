<?php

include("connection.php");
session_start();

// --- 1. GET CURRENT STATE ---
// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php"); // Not logged in, send back to index
    exit();
}

// Get all session data
$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$current_story_id = $_SESSION['story_state'];
$visited_stories = $_SESSION['visited_stories'] ?? []; // Get visited list
$total_score = $_SESSION['total_score'] ?? 0;       // Get current score


// --- 2. GAME OVER CHECK ---
// If the current story is 0, the game is over.
if ($current_story_id == 0) {

    // Fetch final progress
    $sqlProgress = "SELECT game_progress FROM progression WHERE user_id = ?";
    $stmt = $conn->prepare($sqlProgress);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $sqlProgressFetch = $stmt->get_result()->fetch_assoc();
    $final_progress = $sqlProgressFetch['game_progress'];

    // Display "Game Over" screen
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Game Over</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #111; color: #f00; text-align: center; }
            .game-over-box { max-width: 500px; margin: 10vh auto; padding: 20px; background-color: #222; border: 2px solid #f00; border-radius: 15px; box-shadow: 0 0 20px #f00; }
            h1 { font-size: 4rem; margin: 0; }
            h2 { font-size: 2rem; color: #fff; }
            p { color: #ccc; max-height: 200px; overflow-y: auto; text-align: left; padding: 10px; background: #1a1a1a; border-radius: 5px; }
            a { display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #f00; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; }
            a:hover { background-color: #c00; }
        </style>
    </head>
    <body>
        <div class="game-over-box">
            <h1>You Died!</h1>
            <h2>Total Score: ' . $total_score . '</h2>
            <hr>
            <h3>Final Story:</h3>
            <p>' . htmlspecialchars($final_progress) . '</p>
            <a href="index.php">Play Again</a>
        </div>
    </body>
    </html>';

    // Stop the script. Do not load the rest of the game.
    exit();
}


// --- 3. THE GAME CLASS ---
class game
{
    public $conn;
    public $user_id;
    public $username;
    public $Choice1;
    public $Choice2;
    public $thoughts;
    public $story;

    public $current_story_id;
    public $visited_stories; // Array of visited story IDs
    public $total_score;

    public function __construct($conn, $user_id, $username, $story_id, $visited, $score)
    {
        $this->conn = $conn;
        $this->user_id = $user_id;
        $this->username = $username;
        $this->current_story_id = $story_id;
        $this->visited_stories = $visited;
        $this->total_score = $score;
    }

    // Loads choices for the *current* story ID (used on page load)
    public function loadCurrentChoices()
    {
        $sql = "SELECT * FROM story WHERE story_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->current_story_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        $this->Choice1 = $row["choice1"];
        $this->Choice2 = $row["choice2"];
    }

    // Process Choice 1: Update progress and add points
    public function choice1()
    {
        $sql = "SELECT * FROM story WHERE story_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->current_story_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        $this->Choice1 = $row["choice1"];
        $this->thoughts = $row["thoughts"];
        $points = (int) $row["c1_points"]; // Get points for choice 1

        // Update progress and score in one query
        $sql_update = "UPDATE progression 
                       SET game_progress = CONCAT(game_progress, ' ', ?, ' ', ?, ' '), 
                           score_sum = score_sum + ? 
                       WHERE user_id = ?";

        $stmt_update = $this->conn->prepare($sql_update);
        $stmt_update->bind_param("ssii", $this->Choice1, $this->thoughts, $points, $this->user_id);
        $stmt_update->execute();

        // Update score in session
        $_SESSION['total_score'] = $this->total_score + $points;
    }

    // Process Choice 2: Update progress and add points
    public function choice2()
    {
        $sql = "SELECT * FROM story WHERE story_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $this->current_story_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        $this->Choice2 = $row["choice2"];
        $this->thoughts = $row["thoughts"];
        $points = (int) $row["c2_points"]; // Get points for choice 2

        // Update progress and score in one query
        $sql_update = "UPDATE progression 
                       SET game_progress = CONCAT(game_progress, ' ', ?, ' ', ?, ' '), 
                           score_sum = score_sum + ? 
                       WHERE user_id = ?";

        $stmt_update = $this->conn->prepare($sql_update);
        $stmt_update->bind_param("ssii", $this->Choice2, $this->thoughts, $points, $this->user_id);
        $stmt_update->execute();

        // Update score in session
        $_SESSION['total_score'] = $this->total_score + $points;
    }

    // Finds the next story
    public function getNextStory()
    {
        // 1. Define all possible stories (IDs 1 through 10)
        $all_stories = range(1, 10);

        // 2. Find which stories are *not* in the visited list
        $available_stories = array_diff($all_stories, $this->visited_stories);

        $RNG = 0; // Default to 0 (Game Over)

        if (empty($available_stories)) {
            // 3a. No more stories left. Set RNG to 0 (Game Over)
            $RNG = 0;
        } else {
            // 3b. Stories are available. Pick one at random.
            $RNG = $available_stories[array_rand($available_stories)];
        }

        // 4. Add the new story (even 0) to the visited list to prevent re-use
        $_SESSION['visited_stories'][] = $RNG;

        // 5. If the game is not over, add the new story text to the log
        if ($RNG != 0) {
            $sqlChoice = "SELECT * FROM story WHERE story_id = ?";
            $stmt = $this->conn->prepare($sqlChoice);
            $stmt->bind_param("i", $RNG);
            $stmt->execute();
            $sqlChoiceFetch = $stmt->get_result()->fetch_assoc();

            // Set choices for the *next* screen
            $this->Choice1 = $sqlChoiceFetch["choice1"];
            $this->Choice2 = $sqlChoiceFetch["choice2"];
            $StoryUpdate = $sqlChoiceFetch['story']; // The new story text

            // Append the *new* story text to the progress
            $updateStory = "UPDATE progression SET game_progress = CONCAT(game_progress, ?, ' ') WHERE user_id = ?";
            $stmt_update = $this->conn->prepare($updateStory);
            $stmt_update->bind_param("si", $StoryUpdate, $this->user_id);
            $stmt_update->execute();
        }

        // 6. Return the new story_id (0 or a new ID)
        return $RNG;
    }

    // Main logic controller
    public function gameLogic($choice)
    {
        // 1. Process the user's *current* choice (1 or 2)
        if ($choice == 1) {
            $this->choice1();
        } elseif ($choice == 2) {
            $this->choice2();
        }

        // 2. Get the *next* story, update progress, and get its ID
        $new_story_id = $this->getNextStory();

        // 3. Save the new story state to the session
        $_SESSION['story_state'] = $new_story_id;
    }
}

// --- 4. CREATE GAME OBJECT ---
// Pass all the current state data to the game object
$action = new game($conn, $user_id, $username, $current_story_id, $visited_stories, $total_score);


// --- 5. RUN LOGIC ---
if (isset($_POST['submit'])) {
    // --- User clicked submit ---
    $choice = $_POST['choice'];
    $action->gameLogic($choice); // This processes the choice AND gets the next story

    // After gameLogic runs, $action->Choice1 and $action->Choice2
    // have been updated with the *new* choices for the next screen.
    $Choice1 = $action->Choice1;
    $Choice2 = $action->Choice2;

} else {
    // --- Page is just loading, not a submission ---
    // We just need to load the choices for the *current* story
    $action->loadCurrentChoices();
    $Choice1 = $action->Choice1;
    $Choice2 = $action->Choice2;
}

// --- 6. GET CURRENT PROGRESS FOR DISPLAY ---
// We do this *after* all logic to show the most up-to-date story
$sqlProgress = "SELECT game_progress FROM progression WHERE user_id = ?";
$stmt = $conn->prepare($sqlProgress);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sqlProgressFetch = $stmt->get_result()->fetch_assoc();
$game_progress_text = $sqlProgressFetch['game_progress'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2D Story Game</title>
    <style>
        .game_window {
            margin-top: 10vh;
            max-width: 500px;
            min-height: 40vh;
            margin: auto;
            border: 1px solid black;
            padding: 15px;
            /* max-height: 400px; */
            /* overflow-y: scroll; */
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
    <div style="padding: 50px;">&nbsp;</div>
    <div class="game_window">
        <!-- Removed the new header bar -->

        <div class="game_section_01">
            <h2 class="align_center border_top">Game Visuals</h2>
            <hr>
            <div>
                <p class="align_center">(some random graphics)</p>
            </div>
        </div>

        <div class="game_section_02">
            <h2 class="align_center border_top">Story</h2>
            <hr>
            <!-- Reverted to original story box style -->
            <div style="max-height:200px; overflow-y: scroll;">
                <?php
                echo $game_progress_text;
                ?>
            </div>
        </div>

        <div class="game_section_02"> <!-- Used game_section_02 to match original spacing -->
            <h2 class="align_center border_top">Choice Buttons</h2>
            <hr>
            <form method="POST">
                <br><br>
                <div style="padding-left:20px;">
                    <!-- Removed htmlspecialchars() -->
                    <input type="radio" value="1" id="choice1" name="choice" class="click" required>
                    <label for="choice1">&nbsp;<?php echo $Choice1; ?></label>
                    <br><br>
                    <input type="radio" value="2" id="choice2" name="choice" class="click" required>
                    <label for="choice2">&nbsp;<?php echo $Choice2; ?></label>
                    <br><br>
                </div>
                <input type="submit" name="submit" class="align_center border_button" value="Confirm Choice">
            </form>
        </div>
    </div>
</body>

</html>