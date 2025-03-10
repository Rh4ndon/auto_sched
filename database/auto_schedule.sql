-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 10, 2025 at 01:15 PM
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
-- Database: `auto_schedule`
--

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `id` int(11) NOT NULL,
  `room_number` varchar(50) NOT NULL,
  `capacity` int(11) NOT NULL,
  `type` enum('Room','Laboratory','Gym') NOT NULL DEFAULT 'Room'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classrooms`
--

INSERT INTO `classrooms` (`id`, `room_number`, `capacity`, `type`) VALUES
(8, '101', 35, 'Room'),
(9, '202', 30, 'Room'),
(10, '303', 30, 'Room'),
(11, '404', 30, 'Room'),
(12, '505', 30, 'Room'),
(13, '606', 20, 'Laboratory'),
(14, '01', 50, 'Gym');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `semester` enum('1','2','midyear') NOT NULL,
  `year_level` int(11) NOT NULL,
  `academic_year` varchar(12) NOT NULL,
  `units` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `subject_id`, `section_id`, `semester`, `year_level`, `academic_year`, `units`) VALUES
(503, 10, 22, 1, '1', 1, '2024-2025', 3),
(504, 10, 23, 1, '1', 1, '2024-2025', 3),
(505, 10, 24, 1, '1', 1, '2024-2025', 3),
(506, 10, 25, 1, '1', 1, '2024-2025', 3),
(507, 10, 26, 1, '1', 1, '2024-2025', 3),
(508, 10, 27, 1, '1', 1, '2024-2025', 2),
(509, 10, 28, 1, '1', 1, '2024-2025', 2),
(510, 10, 29, 1, '1', 1, '2024-2025', 1),
(511, 10, 30, 1, '1', 1, '2024-2025', 2),
(512, 10, 31, 1, '1', 1, '2024-2025', 1),
(513, 4, 22, 2, '1', 1, '2024-2025', 3),
(514, 4, 23, 2, '1', 1, '2024-2025', 3),
(515, 4, 24, 2, '1', 1, '2024-2025', 3),
(516, 4, 25, 2, '1', 1, '2024-2025', 3),
(517, 4, 26, 2, '1', 1, '2024-2025', 3),
(518, 4, 27, 2, '1', 1, '2024-2025', 2),
(519, 4, 28, 2, '1', 1, '2024-2025', 2),
(520, 4, 29, 2, '1', 1, '2024-2025', 1),
(521, 4, 30, 2, '1', 1, '2024-2025', 2),
(522, 4, 31, 2, '1', 1, '2024-2025', 1),
(523, 2, 22, 6, '1', 1, '2024-2025', 3),
(524, 2, 23, 6, '1', 1, '2024-2025', 3),
(525, 2, 24, 6, '1', 1, '2024-2025', 3),
(526, 2, 25, 6, '1', 1, '2024-2025', 3),
(527, 2, 26, 6, '1', 1, '2024-2025', 3),
(528, 2, 27, 6, '1', 1, '2024-2025', 2),
(529, 2, 28, 6, '1', 1, '2024-2025', 2),
(530, 2, 29, 6, '1', 1, '2024-2025', 1),
(531, 2, 30, 6, '1', 1, '2024-2025', 2),
(532, 2, 31, 6, '1', 1, '2024-2025', 1),
(533, 24, 22, 4, '1', 1, '2024-2025', 3),
(534, 24, 23, 4, '1', 1, '2024-2025', 3),
(535, 24, 24, 4, '1', 1, '2024-2025', 3),
(536, 24, 25, 4, '1', 1, '2024-2025', 3),
(537, 24, 26, 4, '1', 1, '2024-2025', 3),
(538, 24, 27, 4, '1', 1, '2024-2025', 2),
(539, 24, 28, 4, '1', 1, '2024-2025', 2),
(540, 24, 29, 4, '1', 1, '2024-2025', 1),
(541, 24, 30, 4, '1', 1, '2024-2025', 2),
(542, 24, 31, 4, '1', 1, '2024-2025', 1);

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `classroom_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `semester` enum('1','2','midyear') NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `exam_type` enum('none','prelim','midterm','final') DEFAULT 'none',
  `subject_type` enum('lecture','lab','pe') NOT NULL DEFAULT 'lecture'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `subject_id`, `teacher_id`, `classroom_id`, `section_id`, `day`, `start_time`, `end_time`, `semester`, `academic_year`, `exam_type`, `subject_type`) VALUES
(6082, 24, 20, 8, 1, 'Monday', '07:00:00', '08:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6083, 22, 21, 9, 1, 'Monday', '09:30:00', '11:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6084, 28, 8, 10, 1, 'Monday', '13:00:00', '14:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6085, 25, 22, 11, 1, 'Monday', '15:30:00', '17:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6086, 27, 3, 14, 1, 'Tuesday', '07:00:00', '08:30:00', '1', '2024-2025', 'prelim', 'pe'),
(6087, 29, 8, 13, 1, 'Tuesday', '09:30:00', '11:00:00', '1', '2024-2025', 'prelim', 'lab'),
(6088, 30, 16, 8, 1, 'Tuesday', '13:00:00', '14:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6089, 23, 23, 9, 1, 'Tuesday', '15:30:00', '17:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6090, 26, 23, 8, 1, 'Wednesday', '07:00:00', '08:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6091, 31, 16, 13, 1, 'Wednesday', '09:30:00', '11:00:00', '1', '2024-2025', 'prelim', 'lab'),
(6092, 24, 20, 8, 2, 'Monday', '09:30:00', '11:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6093, 27, 3, 14, 2, 'Monday', '07:00:00', '08:30:00', '1', '2024-2025', 'prelim', 'pe'),
(6094, 26, 23, 9, 2, 'Monday', '13:00:00', '14:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6095, 22, 21, 10, 2, 'Monday', '15:30:00', '17:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6096, 28, 8, 8, 2, 'Tuesday', '07:00:00', '08:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6097, 30, 16, 9, 2, 'Tuesday', '09:30:00', '11:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6098, 25, 22, 10, 2, 'Tuesday', '13:00:00', '14:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6099, 31, 16, 13, 2, 'Tuesday', '15:30:00', '17:00:00', '1', '2024-2025', 'prelim', 'lab'),
(6100, 29, 8, 13, 2, 'Wednesday', '07:00:00', '08:30:00', '1', '2024-2025', 'prelim', 'lab'),
(6101, 23, 23, 8, 2, 'Wednesday', '09:30:00', '11:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6102, 28, 8, 8, 4, 'Monday', '15:30:00', '17:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6103, 30, 16, 9, 4, 'Monday', '07:00:00', '08:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6104, 26, 23, 10, 4, 'Monday', '09:30:00', '11:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6105, 31, 16, 13, 4, 'Monday', '13:00:00', '14:30:00', '1', '2024-2025', 'prelim', 'lab'),
(6106, 29, 8, 13, 4, 'Tuesday', '13:00:00', '14:30:00', '1', '2024-2025', 'prelim', 'lab'),
(6107, 23, 23, 8, 4, 'Tuesday', '09:30:00', '11:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6108, 22, 21, 9, 4, 'Tuesday', '07:00:00', '08:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6109, 25, 22, 10, 4, 'Tuesday', '15:30:00', '17:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6110, 24, 20, 8, 4, 'Wednesday', '13:00:00', '14:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6111, 27, 3, 14, 4, 'Wednesday', '07:00:00', '08:30:00', '1', '2024-2025', 'prelim', 'pe'),
(6112, 31, 16, 13, 6, 'Monday', '09:30:00', '11:00:00', '1', '2024-2025', 'prelim', 'lab'),
(6113, 26, 23, 9, 6, 'Monday', '15:30:00', '17:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6114, 25, 22, 8, 6, 'Monday', '13:00:00', '14:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6115, 29, 8, 13, 6, 'Wednesday', '13:00:00', '14:30:00', '1', '2024-2025', 'prelim', 'lab'),
(6116, 24, 20, 8, 6, 'Tuesday', '15:30:00', '17:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6117, 30, 16, 10, 6, 'Tuesday', '07:00:00', '08:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6118, 28, 8, 10, 6, 'Monday', '07:00:00', '08:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6119, 23, 23, 9, 6, 'Tuesday', '13:00:00', '14:30:00', '1', '2024-2025', 'prelim', 'lecture'),
(6120, 22, 21, 11, 6, 'Tuesday', '09:30:00', '11:00:00', '1', '2024-2025', 'prelim', 'lecture'),
(6121, 27, 3, 14, 6, 'Wednesday', '09:30:00', '11:00:00', '1', '2024-2025', 'prelim', 'pe'),
(6430, 27, 3, 14, 1, 'Monday', '07:00:00', '08:00:00', '1', '2024-2025', 'none', 'pe'),
(6431, 26, 23, 8, 1, 'Tuesday', '07:00:00', '08:00:00', '1', '2024-2025', 'none', 'lecture'),
(6432, 26, 23, 8, 1, 'Wednesday', '07:00:00', '08:00:00', '1', '2024-2025', 'none', 'lecture'),
(6433, 26, 23, 8, 1, 'Thursday', '07:00:00', '08:00:00', '1', '2024-2025', 'none', 'lecture'),
(6434, 24, 20, 9, 1, 'Tuesday', '09:00:00', '10:00:00', '1', '2024-2025', 'none', 'lecture'),
(6435, 24, 20, 9, 1, 'Wednesday', '09:00:00', '10:00:00', '1', '2024-2025', 'none', 'lecture'),
(6436, 24, 20, 9, 1, 'Thursday', '09:00:00', '10:00:00', '1', '2024-2025', 'none', 'lecture'),
(6437, 31, 16, 13, 1, 'Monday', '09:00:00', '10:30:00', '1', '2024-2025', 'none', 'lab'),
(6438, 29, 8, 13, 1, 'Tuesday', '13:00:00', '14:30:00', '1', '2024-2025', 'none', 'lab'),
(6439, 22, 21, 8, 1, 'Monday', '11:00:00', '12:00:00', '1', '2024-2025', 'none', 'lecture'),
(6440, 22, 21, 10, 1, 'Wednesday', '11:00:00', '12:00:00', '1', '2024-2025', 'none', 'lecture'),
(6441, 22, 21, 10, 1, 'Thursday', '11:00:00', '12:00:00', '1', '2024-2025', 'none', 'lecture'),
(6442, 25, 22, 9, 1, 'Monday', '13:00:00', '14:00:00', '1', '2024-2025', 'none', 'lecture'),
(6443, 25, 22, 11, 1, 'Wednesday', '13:00:00', '14:00:00', '1', '2024-2025', 'none', 'lecture'),
(6444, 25, 22, 11, 1, 'Thursday', '13:00:00', '14:00:00', '1', '2024-2025', 'none', 'lecture'),
(6445, 23, 23, 10, 1, 'Tuesday', '11:00:00', '12:00:00', '1', '2024-2025', 'none', 'lecture'),
(6446, 23, 23, 10, 1, 'Monday', '15:00:00', '16:00:00', '1', '2024-2025', 'none', 'lecture'),
(6447, 23, 23, 12, 1, 'Wednesday', '15:00:00', '16:00:00', '1', '2024-2025', 'none', 'lecture'),
(6448, 28, 8, 11, 1, 'Tuesday', '15:00:00', '16:30:00', '1', '2024-2025', 'none', 'lecture'),
(6449, 30, 16, 12, 1, 'Thursday', '15:00:00', '16:30:00', '1', '2024-2025', 'none', 'lecture'),
(6450, 31, 16, 13, 2, 'Thursday', '07:00:00', '08:30:00', '1', '2024-2025', 'none', 'lab'),
(6451, 28, 8, 8, 2, 'Tuesday', '09:00:00', '10:30:00', '1', '2024-2025', 'none', 'lecture'),
(6452, 27, 3, 14, 2, 'Monday', '09:00:00', '10:00:00', '1', '2024-2025', 'none', 'pe'),
(6453, 25, 22, 8, 2, 'Wednesday', '09:00:00', '10:00:00', '1', '2024-2025', 'none', 'lecture'),
(6454, 25, 22, 8, 2, 'Monday', '07:00:00', '08:00:00', '1', '2024-2025', 'none', 'lecture'),
(6455, 25, 22, 9, 2, 'Tuesday', '07:00:00', '08:00:00', '1', '2024-2025', 'none', 'lecture'),
(6456, 24, 20, 9, 2, 'Wednesday', '07:00:00', '08:00:00', '1', '2024-2025', 'none', 'lecture'),
(6457, 24, 20, 8, 2, 'Thursday', '11:00:00', '12:00:00', '1', '2024-2025', 'none', 'lecture'),
(6458, 24, 20, 9, 2, 'Monday', '11:00:00', '12:00:00', '1', '2024-2025', 'none', 'lecture'),
(6459, 29, 8, 13, 2, 'Wednesday', '13:00:00', '14:30:00', '1', '2024-2025', 'none', 'lab'),
(6460, 23, 23, 9, 2, 'Thursday', '13:00:00', '14:00:00', '1', '2024-2025', 'none', 'lecture'),
(6461, 23, 23, 10, 2, 'Tuesday', '13:00:00', '14:00:00', '1', '2024-2025', 'none', 'lecture'),
(6462, 23, 23, 10, 2, 'Wednesday', '16:00:00', '17:00:00', '1', '2024-2025', 'none', 'lecture'),
(6463, 22, 21, 10, 2, 'Thursday', '09:00:00', '10:00:00', '1', '2024-2025', 'none', 'lecture'),
(6464, 22, 21, 11, 2, 'Tuesday', '11:00:00', '12:00:00', '1', '2024-2025', 'none', 'lecture'),
(6465, 22, 21, 10, 2, 'Monday', '13:00:00', '14:00:00', '1', '2024-2025', 'none', 'lecture'),
(6466, 26, 23, 11, 2, 'Thursday', '15:00:00', '16:00:00', '1', '2024-2025', 'none', 'lecture'),
(6467, 26, 23, 12, 2, 'Tuesday', '15:00:00', '16:00:00', '1', '2024-2025', 'none', 'lecture'),
(6468, 26, 23, 11, 2, 'Monday', '16:00:00', '17:00:00', '1', '2024-2025', 'none', 'lecture'),
(6469, 30, 16, 8, 4, 'Wednesday', '13:00:00', '14:30:00', '1', '2024-2025', 'none', 'lecture'),
(6470, 26, 23, 8, 4, 'Thursday', '09:00:00', '10:00:00', '1', '2024-2025', 'none', 'lecture'),
(6471, 26, 23, 8, 4, 'Tuesday', '16:00:00', '17:00:00', '1', '2024-2025', 'none', 'lecture'),
(6472, 26, 23, 8, 4, 'Monday', '09:00:00', '10:00:00', '1', '2024-2025', 'none', 'lecture'),
(6473, 24, 20, 9, 4, 'Thursday', '07:00:00', '08:00:00', '1', '2024-2025', 'none', 'lecture'),
(6474, 24, 20, 9, 4, 'Tuesday', '11:00:00', '12:00:00', '1', '2024-2025', 'none', 'lecture'),
(6475, 24, 20, 9, 4, 'Monday', '07:00:00', '08:00:00', '1', '2024-2025', 'none', 'lecture'),
(6476, 28, 8, 9, 4, 'Wednesday', '15:00:00', '16:30:00', '1', '2024-2025', 'none', 'lecture'),
(6477, 27, 3, 14, 4, 'Wednesday', '07:00:00', '08:00:00', '1', '2024-2025', 'none', 'pe'),
(6478, 25, 22, 10, 4, 'Thursday', '15:00:00', '16:00:00', '1', '2024-2025', 'none', 'lecture'),
(6479, 25, 22, 10, 4, 'Tuesday', '09:00:00', '10:00:00', '1', '2024-2025', 'none', 'lecture'),
(6480, 25, 22, 10, 4, 'Monday', '11:00:00', '12:00:00', '1', '2024-2025', 'none', 'lecture'),
(6481, 23, 23, 11, 4, 'Thursday', '11:00:00', '12:00:00', '1', '2024-2025', 'none', 'lecture'),
(6482, 23, 23, 11, 4, 'Monday', '13:00:00', '14:00:00', '1', '2024-2025', 'none', 'lecture'),
(6483, 23, 23, 10, 4, 'Wednesday', '09:00:00', '10:00:00', '1', '2024-2025', 'none', 'lecture'),
(6484, 22, 21, 11, 4, 'Tuesday', '07:00:00', '08:00:00', '1', '2024-2025', 'none', 'lecture'),
(6485, 22, 21, 12, 4, 'Thursday', '13:00:00', '14:00:00', '1', '2024-2025', 'none', 'lecture'),
(6486, 22, 21, 12, 4, 'Monday', '15:00:00', '16:00:00', '1', '2024-2025', 'none', 'lecture'),
(6487, 31, 16, 13, 4, 'Monday', '16:00:00', '17:30:00', '1', '2024-2025', 'none', 'lab'),
(6488, 29, 8, 13, 4, 'Thursday', '16:00:00', '17:30:00', '1', '2024-2025', 'none', 'lab'),
(6489, 22, 21, 8, 6, 'Tuesday', '13:00:00', '14:00:00', '1', '2024-2025', 'none', 'lecture'),
(6490, 22, 21, 8, 6, 'Wednesday', '15:00:00', '16:00:00', '1', '2024-2025', 'none', 'lecture'),
(6491, 22, 21, 8, 6, 'Thursday', '15:00:00', '16:00:00', '1', '2024-2025', 'none', 'lecture'),
(6492, 29, 8, 13, 6, 'Monday', '07:00:00', '08:30:00', '1', '2024-2025', 'none', 'lab'),
(6493, 30, 16, 8, 6, 'Monday', '13:00:00', '14:30:00', '1', '2024-2025', 'none', 'lecture'),
(6494, 24, 20, 9, 6, 'Tuesday', '15:00:00', '16:00:00', '1', '2024-2025', 'none', 'lecture'),
(6495, 24, 20, 9, 6, 'Wednesday', '11:00:00', '12:00:00', '1', '2024-2025', 'none', 'lecture'),
(6496, 24, 20, 9, 6, 'Thursday', '16:00:00', '17:00:00', '1', '2024-2025', 'none', 'lecture'),
(6497, 25, 22, 10, 6, 'Tuesday', '16:00:00', '17:00:00', '1', '2024-2025', 'none', 'lecture'),
(6498, 25, 22, 10, 6, 'Wednesday', '07:00:00', '08:00:00', '1', '2024-2025', 'none', 'lecture'),
(6499, 25, 22, 10, 6, 'Thursday', '07:00:00', '08:00:00', '1', '2024-2025', 'none', 'lecture'),
(6500, 27, 3, 14, 6, 'Monday', '11:00:00', '12:00:00', '1', '2024-2025', 'none', 'pe'),
(6501, 28, 8, 9, 6, 'Monday', '09:00:00', '10:30:00', '1', '2024-2025', 'none', 'lecture'),
(6502, 26, 23, 11, 6, 'Tuesday', '09:00:00', '10:00:00', '1', '2024-2025', 'none', 'lecture'),
(6503, 26, 23, 12, 6, 'Wednesday', '13:00:00', '14:00:00', '1', '2024-2025', 'none', 'lecture'),
(6504, 31, 16, 13, 6, 'Thursday', '09:00:00', '10:30:00', '1', '2024-2025', 'none', 'lab');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `year_level` int(11) NOT NULL,
  `semester` enum('1','2','midyear') NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `student_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `section_name`, `year_level`, `semester`, `academic_year`, `student_count`) VALUES
(1, 'BSIT-1A', 1, '1', '2024-2025', 5),
(2, 'BSIT-1B', 1, '1', '2024-2025', 5),
(4, 'BSIT-1D', 1, '1', '2024-2025', 4),
(6, 'BSIT-1C', 1, '1', '2024-2025', 4);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_code` varchar(50) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `semester` enum('1','2','midyear') NOT NULL,
  `year_level` int(11) NOT NULL,
  `subject_type` enum('lecture','lab','pe') NOT NULL DEFAULT 'lecture',
  `minutes_per_week` int(11) NOT NULL DEFAULT 60,
  `units` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_code`, `subject_name`, `semester`, `year_level`, `subject_type`, `minutes_per_week`, `units`) VALUES
(22, 'UTS-101', 'Understanding The Self', '1', 1, 'lecture', 180, 3),
(23, 'TCW', 'The Contemporary World', '1', 1, 'lecture', 180, 3),
(24, 'AAP', 'Art App', '1', 1, 'lecture', 180, 3),
(25, 'MATH-100', 'Mathematics', '1', 1, 'lecture', 180, 3),
(26, 'PC-102', 'Pure Communication', '1', 1, 'lecture', 180, 3),
(27, 'PE-1', 'Phisical Education 1', '1', 1, 'pe', 60, 2),
(28, 'PH-100', 'Physics', '1', 1, 'lecture', 90, 2),
(29, 'PH-100L', 'Physics Lab', '1', 1, 'lab', 90, 1),
(30, 'CHEM', 'Chemistry', '1', 1, 'lecture', 90, 2),
(31, 'CHEML', 'Chemistry Lab', '1', 1, 'lab', 90, 1);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subjects`
--

CREATE TABLE `teacher_subjects` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `semester` enum('1','2','midyear') NOT NULL,
  `academic_year` varchar(12) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_subjects`
--

INSERT INTO `teacher_subjects` (`id`, `teacher_id`, `subject_id`, `semester`, `academic_year`) VALUES
(36, 21, 22, '1', '2024-2025'),
(38, 20, 24, '1', '2024-2025'),
(39, 22, 25, '1', '2024-2025'),
(40, 3, 27, '1', '2024-2025'),
(41, 23, 23, '1', '2024-2025'),
(42, 23, 26, '1', '2024-2025'),
(43, 8, 28, '1', '2024-2025'),
(44, 8, 29, '1', '2024-2025'),
(45, 16, 30, '1', '2024-2025'),
(46, 16, 31, '1', '2024-2025');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_type` enum('admin','student','teacher') NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_type`, `name`, `email`, `password`, `gender`) VALUES
(1, 'admin', 'Admin Admin', 'admin@gmail.com', '036d0ef7567a20b5a4ad24a354ea4a945ddab676', 'Male'),
(2, 'student', 'Sample Studentss', 'samplesss@gmail.com', 'a346bc80408d9b2a5063fd1bddb20e2d5586ec30', 'Male'),
(3, 'teacher', 'Teacher', 'teacher@gmail.com', 'a346bc80408d9b2a5063fd1bddb20e2d5586ec30', 'Female'),
(4, 'student', 'John Doe', 'johndoe@example.com', '10470c3b4b1fed12c3baac014be15fac67c6e815', 'Male'),
(5, 'student', 'Jane Smith', 'jane@example.com', '10470c3b4b1fed12c3baac014be15fac67c6e815', 'Female'),
(8, 'teacher', 'Sample Teacher', 'teachersample@gmail.com', '10470c3b4b1fed12c3baac014be15fac67c6e815', 'Female'),
(10, 'student', 'Another Sutudent', 'another@gmail.com', '10470c3b4b1fed12c3baac014be15fac67c6e815', 'Male'),
(12, 'student', 'Jane Foster', 'janefoster@example.com', '10470c3b4b1fed12c3baac014be15fac67c6e815', 'Female'),
(15, 'student', 'New Sample Student', 'new@gmail.com', '10470c3b4b1fed12c3baac014be15fac67c6e815', 'Female'),
(16, 'teacher', 'Some Teacher', 'some@gmail.com', '10470c3b4b1fed12c3baac014be15fac67c6e815', 'Female'),
(20, 'teacher', 'New Teaccher', 'newteacher@gmail.com', '10470c3b4b1fed12c3baac014be15fac67c6e815', 'Male'),
(21, 'teacher', 'Another Teacher', 'anotherteacher@gmail.com', '10470c3b4b1fed12c3baac014be15fac67c6e815', 'Male'),
(22, 'teacher', 'Physics Techer', 'physics@gmail.com', '10470c3b4b1fed12c3baac014be15fac67c6e815', 'Male'),
(23, 'teacher', 'New PE Teacher', 'peteach@gmail.com', '10470c3b4b1fed12c3baac014be15fac67c6e815', 'Male'),
(24, 'student', 'Dora', 'dora@gmail.com', '10470c3b4b1fed12c3baac014be15fac67c6e815', 'Female');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `classroom_id` (`classroom_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=551;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6505;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_3` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`);

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `schedules_ibfk_3` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`),
  ADD CONSTRAINT `schedules_ibfk_4` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`);

--
-- Constraints for table `teacher_subjects`
--
ALTER TABLE `teacher_subjects`
  ADD CONSTRAINT `teacher_subjects_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
