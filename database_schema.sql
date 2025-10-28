-- --- database_schema.sql (Example MySQL table) ---
-- This is the SQL command you would run in your MySQL database
-- (e.g., in phpMyAdmin) to create the table.

CREATE DATABASE IF NOT EXISTS game_scores;

USE game_scores;

CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    score INT NOT NULL,
    won BOOLEAN NOT NULL DEFAULT 0,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add an index for faster searching by user_id
CREATE INDEX idx_user_id ON scores (user_id);
