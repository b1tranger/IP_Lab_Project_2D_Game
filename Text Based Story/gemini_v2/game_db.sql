-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 03:02 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `game_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `game_scores`
--

CREATE TABLE `game_scores` (
  `id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `summary` text NOT NULL,
  `ending_type` varchar(50) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `progression`
--

CREATE TABLE `progression` (
  `user_id` int(10) NOT NULL,
  `username` varchar(256) NOT NULL,
  `game_progress` text NOT NULL,
  `score_sum` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `story`
--

CREATE TABLE `story` (
  `story_id` int(10) NOT NULL,
  `story` text NOT NULL,
  `choice1` text NOT NULL,
  `choice2` text NOT NULL,
  `c1_points` int(10) NOT NULL,
  `c2_points` int(10) NOT NULL,
  `thoughts` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `story`
--

INSERT INTO `story` (`story_id`, `story`, `choice1`, `choice2`, `c1_points`, `c2_points`, `thoughts`) VALUES
(1, 'The sky is dark today, it may rain soon. ', 'You went to sleep', 'Mere rain tries to stop my noble journey? ', 5, 30, 'You did not regret it, but the author decided to curse you anyway. '),
(2, 'You remembered that you are a warrior. Are you well-equipped? ', 'You have a knife. ', 'You have a shield. ', 10, 15, 'Sometimes a good offence is the best defence. Only sometimes, that is... '),
(3, 'The start of the day was tough. ', 'You hadn\'t had breakfast. ', 'You had a stomach ache from last night\'s meal. ', 5, 5, 'Is it a bad omen? '),
(4, 'While treading the rough road, you had a wild encounter. ', 'Is it a butterfly? ', 'You see a goblin. ', 10, 5, 'It was actually a Politician who lost an election. '),
(5, 'You recall that you had a wild encounter. But what did you do with it? ', 'You admired the state it was in and felt blessed. ', 'You kicked and bashed it to make it more miserable than it seemed to you. ', 20, 10, 'Somehow it felt well-deserved. '),
(6, 'The nation was in a dire state. ', 'You chose to remain for any unforeseen circumstances. ', 'You chose to ditch it... Just because you can! ', 15, 10, 'Sometimes your feelings do take priority. '),
(7, 'Past the river, you met a beautiful person. ', 'You greeted them and marched on. ', 'You marched on. ', 15, 5, 'Would such a beautiful person greet you back? Even gift you something? The author did not bother to add that. '),
(8, 'You remember a certain lunchtime, you had a great meal. ', 'You ate your fill. Almost too much. ', 'You had a bad feeling about how good it was. ', 5, 5, 'Was it a good or a bad one? '),
(9, 'A pouch of goods fell from a traveller. ', 'You returned it, and they thanked you for it. ', 'You inspected it before returning. But you took too long, and they were gone. ', 15, 0, 'A good deed did I do today? '),
(10, 'You found an orphan crying at the roadside. Seems like it lost some money it had. ', 'You walked away. ', 'You got some money out of your own pouch, and gave it away, saying you found it lying on the road. ', 1, 2, 'Sometimes responsibilities are but choices. '),
(0, 'You Died! ', 'choice0', 'choice0', 0, 0, 'The end always seems so sudden and uninteresting. ');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `progression`
--
ALTER TABLE `progression`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `progression`
--
ALTER TABLE `progression`
  MODIFY `user_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
