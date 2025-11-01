-- This is the SQL command to create the table required by the PHP script.-- You can run this command in your database management tool (like phpMyAdmin).-- Make sure you have created a database first (e.g., "game_db").
CREATE TABLE
    game_scores (
        id INT NOT NULL AUTO_INCREMENT,
        score INT NOT NULL,
        summary TEXT NOT NULL,
        ending_type VARCHAR(50) NOT NULL,
        timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    );