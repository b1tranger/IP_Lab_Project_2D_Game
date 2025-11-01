<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... (head content is identical) ... -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Text Adventure</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ... (CSS is identical) ... */
        :root {
            --bg-primary: #11111f;
            --bg-secondary: #22223b;
            --text-primary: #e0e0ff;
            --text-secondary: #a0a0d0;
            --accent-primary: #4a4a88;
            --accent-secondary: #82aaff;
            --btn-bg: #2a2a50;
            --btn-hover: #3a3a70;
            --good-ending: #4CAF50;
            --avg-ending: #FFC107;
            --bad-ending: #F44336;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        #game-container {
            width: 100%;
            max-width: 700px;
            background-color: var(--bg-secondary);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        #score-display {
            padding: 15px 30px;
            background-color: #1a1a2e;
            color: #9a9ad6;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: right;
            border-bottom: 1px solid #404066;
        }

        #story-text {
            padding: 30px;
            font-size: 1.1rem;
            line-height: 1.7;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--accent-primary);
        }

        #choices-container {
            padding: 30px;
            display: grid;
            gap: 15px;
        }

        .choice-btn {
            display: block;
            width: 100%;
            padding: 18px 25px;
            background-color: var(--btn-bg);
            color: var(--text-primary);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-align: left;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
            border-left: 5px solid var(--accent-primary);
        }

        .choice-btn:hover {
            background-color: var(--btn-hover);
            transform: translateY(-2px);
        }

        .choice-btn:active {
            transform: translateY(0);
        }

        #ending-screen {
            padding: 40px;
            text-align: center;
        }

        #ending-screen h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        #ending-screen p {
            color: #d0d0ff;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        #final-score {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--accent-secondary);
            margin-top: 25px;
            margin-bottom: 25px;
            padding: 15px;
            background-color: #1a1a2e;
            border-radius: 8px;
        }

        #save-status {
            font-style: italic;
            color: var(--text-secondary);
            min-height: 1.2em;
        }

        #play-again-btn {
            text-align: center;
            margin-top: 20px;
            font-size: 1.1rem;
            background-color: var(--accent-secondary);
            color: var(--bg-primary);
            border: none;
        }

        #play-again-btn:hover {
            background-color: #a0c0ff;
        }

        .good-ending { color: var(--good-ending); }
        .avg-ending { color: var(--avg-ending); }
        .bad-ending { color: var(--bad-ending); }
    </style>
</head>
<body>

    <div id="game-container">
        <!-- ... (HTML structure is identical) ... -->
        <div id="score-display">Total Score: 0</div>
        <div id="story-text">Loading game...</div>
        <div id="choices-container"></div>
        <div id="ending-screen" style="display: none;">
            <h2 id="ending-result"></h2>
            <p id="ending-summary"></p>
            <p id="final-score"></p>
            <p id="save-status"></p>
            <button id="play-again-btn" class="choice-btn">Play Again</button>
        </div>
    </div>

    <script>
        // ... (Story data is identical) ...
        const storyData = {
            start: {
                text: "You wake up in a dark forest. A faint light flickers in the distance. To your left, you hear a strange rustling in the bushes. What do you do?",
                choices: [
                    { text: "Walk towards the light.", points: 5, nextScene: "light", log: "You decided to walk towards the light." },
                    { text: "Investigate the rustling.", points: -5, nextScene: "rustling", log: "You cautiously moved towards the rustling bushes." },
                    { text: "Wait and listen.", points: 0, nextScene: "wait", log: "You stood still, trying to understand your surroundings." }
                ]
            },
            light: {
                text: "You find a small, cozy cabin. The door is slightly ajar, and a warm glow comes from within. You can smell... soup?",
                choices: [
                    { text: "Knock on the door.", points: 10, nextScene: "cabin_knock", log: "You knocked politely on the cabin door." },
                    { text: "Peek inside quietly.", points: 5, nextScene: "cabin_peek", log: "You decided to peek inside first." },
                    { text: "Turn back to the forest.", points: -5, nextScene: "start", log: "The cabin seemed too risky, so you returned to the forest." }
                ]
            },
            rustling: {
                text: "You push aside the bushes and find a small, glittering amulet on the ground. As you pick it up, a large bear emerges from the trees behind you!",
                choices: [
                    { text: "Try to scare the bear away.", points: -10, nextScene: "check_ending", log: "You waved your arms and yelled, but the bear was not impressed. It charged at you." },
                    { text: "Play dead.", points: 5, nextScene: "bear_plays_dead", log: "You dropped to the ground, holding your breath." },
                    { text: "Throw the amulet and run.", points: 0, nextScene: "start", log: "You threw the shiny object as a distraction and ran back." }
                ]
            },
            wait: {
                text: "You wait for several minutes. The rustling stops, and the light in the distance seems to get brighter. A path you didn't see before appears at your feet.",
                choices: [
                    { text: "Take the new path.", points: 10, nextScene: "path", log: "A new path had appeared, and you decided to take it." },
                    { text: "Ignore the path and go for the light.", points: 5, nextScene: "light", log: "You stuck to your original plan and went for the light." }
                ]
            },
            cabin_knock: {
                text: "An elderly person opens the door with a kind smile. 'Oh, a visitor! Thank goodness. I've made too much soup. Please, come in!'",
                choices: [
                    { text: "Accept the offer.", points: 15, nextScene: "check_ending", log: "You accepted the invitation and had a warm bowl of soup, feeling safe at last." },
                    { text: "Politely decline.", points: 5, nextScene: "check_ending", log: "You politely declined but were given a map and some bread for your journey." }
                ]
            },
            cabin_peek: {
                text: "You peek inside and see the elderly person stirring a pot. They look lonely. Before you can move, they spot you. 'Oh! Don't be shy, come in!'",
                choices: [
                    { text: "Apologize and enter.", points: 10, nextScene: "check_ending", log: "You felt a bit embarrassed but entered. The soup was delicious." },
                    { text: "Get startled and run away.", points: -5, nextScene: "start", log: "You were startled and ran back into the dark forest." }
                ]
            },
            bear_plays_dead: {
                text: "The bear sniffs you curiously, nudges you with its nose, and then huffs, seemingly bored. It wanders off. You are safe... and you still have the amulet.",
                choices: [
                    { text: "Continue on, amulet in hand.", points: 15, nextScene: "check_ending", log: "After the bear left, you picked yourself up and continued, the strange amulet in your hand." }
                ]
            },
            path: {
                text: "The path leads you out of the forest and onto a hill overlooking a peaceful village as the sun rises. You feel a sense of hope.",
                choices: [
                    { text: "Head towards the village.", points: 20, nextScene: "check_ending", log: "The path led you to a hilltop overlooking a peaceful village at sunrise. You were safe." }
                ]
            },
            check_ending: {
                text: "", 
                choices: []
            }
        };

        class Game {
            // ... (constructor is identical) ...
            constructor() {
                this.points = 0;
                this.storyLog = [];
                this.currentScene = 'start';

                this.storyTextElement = document.getElementById("story-text");
                this.choicesContainerElement = document.getElementById("choices-container");
                this.endingScreenElement = document.getElementById("ending-screen");
                this.endingSummaryElement = document.getElementById("ending-summary");
                this.endingResultElement = document.getElementById("ending-result");
                this.finalScoreElement = document.getElementById("final-score");
                this.saveStatusElement = document.getElementById("save-status");
                this.gameContainerElement = document.getElementById("game-container");
                this.scoreDisplayElement = document.getElementById("score-display");
            }
            
            // ... (start, updateScoreDisplay, showScene, makeChoice, triggerEnding are identical) ...
            start() {
                this.points = 0;
                this.storyLog = [];
                this.currentScene = "start";
                this.updateScoreDisplay(); 
                this.scoreDisplayElement.style.display = "block"; 
                this.showScene("start");
            }

            updateScoreDisplay() {
                this.scoreDisplayElement.innerText = `Total Score: ${this.points}`;
            }

            showScene(sceneKey) {
                if (sceneKey === "check_ending") {
                    this.triggerEnding(sceneKey);
                    return;
                }

                const scene = storyData[sceneKey];
                if (!scene) {
                    console.error(`Scene "${sceneKey}" not found!`);
                    return;
                }
                
                this.currentScene = sceneKey;
                this.storyTextElement.innerText = scene.text;
                
                this.choicesContainerElement.innerHTML = '';
                
                scene.choices.forEach((choice, index) => {
                    const button = document.createElement("button");
                    button.innerText = choice.text;
                    button.classList.add("choice-btn");
                    button.addEventListener("click", () => this.makeChoice(index));
                    this.choicesContainerElement.appendChild(button);
                });
            }

            makeChoice(choiceIndex) {
                const scene = storyData[this.currentScene];
                const choice = scene.choices[choiceIndex];

                if (!choice) {
                    console.error(`Invalid choice index: ${choiceIndex}`);
                    return;
                }

                this.points += choice.points;
                this.storyLog.push(choice.log);
                this.updateScoreDisplay(); 
                
                const nextScene = choice.nextScene;
                this.showScene(nextScene);
            }

            triggerEnding(sceneKey) {
                let endingType = "AVERAGE ENDING...";
                let endingClass = "avg-ending";
                
                if (this.points >= 25) {
                    endingType = "GOOD ENDING!!";
                    endingClass = "good-ending";
                } else if (this.points <= -5) {
                    endingType = "BAD ENDING...";
                    endingClass = "bad-ending";
                }
                
                const scene = storyData[this.currentScene];
                const finalSummary = this.storyLog.join(" ") + " " + scene.text;

                this.scoreDisplayElement.style.display = "none";
                this.storyTextElement.style.display = "none";
                this.choicesContainerElement.style.display = "none";

                this.endingResultElement.innerText = endingType;
                this.endingResultElement.className = endingClass;
                this.endingSummaryElement.innerText = finalSummary;
                this.finalScoreElement.innerText = `Your Final Score: ${this.points}`; 
                this.endingScreenElement.style.display = "block";
                
                this.saveScore(this.points, finalSummary, endingType);
            }

            /**
             * Saves the score to the backend using PHP.
             * !! THIS FUNCTION IS UPDATED !!
             * @param {number} score - The final score.
             * @param {string} summary - The final story summary.
             * @param {string} endingType - The ending text.
             */
            async saveScore(score, summary, endingType) {
                this.saveStatusElement.innerText = "Saving score...";

                // --- CHANGE IS HERE ---
                // Instead of JSON, we use URLSearchParams, which acts like a form.
                const formData = new URLSearchParams();
                formData.append('score', score);
                formData.append('summary', summary);
                formData.append('ending_type', endingType);
                // --- END OF CHANGE ---

                try {
                    const response = await fetch('save_score.php', {
                        method: 'POST',
                        // --- CHANGE IS HERE ---
                        // We remove the 'Content-Type': 'application/json' header.
                        // Fetch will automatically set the correct header for URLSearchParams.
                        body: formData
                        // --- END OF CHANGE ---
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    // The *response* from PHP is still JSON (the success/error message),
                    // so this part stays the same.
                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        this.saveStatusElement.innerText = "Score saved!";
                    } else {
                        this.saveStatusElement.innerText = `Error: ${result.message}`;
                    }

                } catch (error) {
                    console.error('Error saving score:', error);
                    this.saveStatusElement.innerText = "Failed to save score. (Check console)";
                }
            }
            
            // ... (setupEventListeners is identical) ...
            setupEventListeners() {
                const playAgainButton = document.getElementById("play-again-btn");
                playAgainButton.addEventListener("click", () => {
                    this.storyTextElement.style.display = "block";
                    this.choicesContainerElement.style.display = "block";
                    this.endingScreenElement.style.display = "none";
                    this.finalScoreElement.innerText = "";
                    this.saveStatusElement.innerText = "";
                    this.start();
                });
            }
        }

        // --- Game Initialization ---
        document.addEventListener('DOMContentLoaded', () => {
            const game = new Game();
            game.setupEventListeners();
            game.start();
        });

    </script>
</body>
</html>

