<?php

include("connection.php");
session_start();

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
$storyState = $_SESSION['story_state'];
echo "| welcome " . $username . ", | ID: " . $user_id . " | ";

for ($i = 0; $i <= 1; $i++) {
    // $storyState = $_SESSION['story_state'];
    $sqlChoice = "SELECT * FROM story WHERE story_id='$storyState'";
    $sqlChoiceQuery = mysqli_query($conn, $sqlChoice);
    $sqlChoiceFetch = mysqli_fetch_assoc($sqlChoiceQuery);
    $Choice1 = $sqlChoiceFetch["choice1"];
    $Choice2 = $sqlChoiceFetch["choice2"];
    $StoryUpdate = $sqlChoiceFetch['story'];
    $updateStory = "UPDATE progression SET game_progress = CONCAT(game_progress, '$StoryUpdate') WHERE user_id = '$user_id'";
}



class game
{
    public $gameStory; // supposed to be an array to store game story
    // public $flag = 0;
    public $conn;
    public $user_id;
    public $username;
    public $Choice1;
    public $Choice2;
    public $thoughts;
    public $story;

    public function __construct($conn, $user_id, $username)
    {
        $this->conn = $conn;
        $this->user_id = $user_id;
        $this->username = $username;
    }
    public function choice1()
    {
        // echo "Selected Choice 1 ";
        $sql = "SELECT * FROM story WHERE user_id='$this->user_id' AND username='$this->username' ";
        $query = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($query);
        $this->Choice1 = $row["choice1"];
        $this->Choice2 = $row["choice2"];
        $this->thoughts = $row["thoughts"];
        $this->story = $row["story"];

        $sql = "UPDATE progression SET game_progress = CONCAT(game_progress, '$this->story','$this->Choice1','$this->thoughts') WHERE user_id = '$this->user_id'";
        $query = mysqli_query($this->conn, $sql);

    }
    public function choice2()
    {
        // echo "Selected Choice 2 ";
        $sql = "SELECT * FROM story WHERE user_id='$this->user_id' AND username='$this->username' ";
        $query = mysqli_query($this->conn, $sql);
        $row = mysqli_fetch_assoc($query);
        $this->Choice1 = $row["choice1"];
        $this->Choice2 = $row["choice2"];
        $this->thoughts = $row["thoughts"];
        $this->story = $row["story"];

        $sql = "UPDATE progression SET game_progress = CONCAT(game_progress, '$this->story','$this->Choice2','$this->thoughts') WHERE user_id = '$this->user_id'";
        $query = mysqli_query($this->conn, $sql);
    }
    public function get()
    {

    }
    public function gameLogic($choice)
    {
        if ($choice == 1) {
            echo "Selected Choice 1";
            $this->choice1();
        } elseif ($choice == 2) {
            echo "Selected Choice 2";
            $this->choice2();
        }
    }
}

$action = new game($conn, $user_id, $username);

if (isset($_POST['submit'])) {
    $choice = $_POST['choice'];
    $sql = "SELECT * FROM story ";
    $action->gameLogic($choice);
    $Choice1 = $action->Choice1;
    $Choice2 = $action->Choice2;
    $thoughts = $action->thoughts;

}

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
    <div style="padding: 50px;">&nbsp;</div>
    <div class="game_window">
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
            <?php
            $sqlProgress = "SELECT game_progress FROM progression WHERE user_id='$user_id' AND username='$username' ";
            $sqlProgressQuery = mysqli_query($conn, $sqlProgress);
            $sqlProgressFetch = mysqli_fetch_assoc($sqlProgressQuery);
            echo $sqlProgressFetch['game_progress'];
            ?>
        </div>
        <div class="game_section_02">
            <h2 class="align_center border_top">Choice Buttons</h2>
            <hr>
            <form method="POST">
                <br><br>
                <div style="padding-left:20px;">

                    <input type="radio" value="1" name="choice" class="click" required>
                    <label>&nbsp;<?php echo $Choice1 ?></label>
                    <br><br>
                    <input type="radio" value="2" name="choice" class="click" required>
                    <label>&nbsp;<?php echo $Choice2 ?></label>
                    <br><br>
                </div>
                <input type="submit" name="submit" class="align_center border_button">
            </form>
        </div>
    </div>
</body>

</html>