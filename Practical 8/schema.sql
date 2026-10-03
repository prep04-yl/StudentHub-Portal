-- =====================================================================
-- Practical 8: MySQL Schema Design, ER Model, PDO Connectivity, and Prepared Statements
-- Database: studenthub_db
-- Entities: students, events, registrations
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `studenthub_db`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `studenthub_db`;

-- Drop existing tables in reverse dependency order
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;

-- ---------------------------------------------------------------------
-- 1. Table: students
-- Stores student user profiles registered in the StudentHub portal
-- ---------------------------------------------------------------------
CREATE TABLE `students` (
    `student_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `mobile` VARCHAR(15) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `course` VARCHAR(80) NOT NULL,
    `academic_year` VARCHAR(20) NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. Table: events
-- Stores campus events and workshops
-- ---------------------------------------------------------------------
CREATE TABLE `events` (
    `event_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `category` ENUM('Technical', 'Workshop', 'Career', 'Cultural') NOT NULL,
    `event_date` DATE NOT NULL,
    `venue` VARCHAR(120) NOT NULL,
    `max_seats` INT UNSIGNED NOT NULL DEFAULT 50,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. Table: registrations
-- Associative entity capturing student registrations for events (Many-to-Many)
-- ---------------------------------------------------------------------
CREATE TABLE `registrations` (
    `registration_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT UNSIGNED NOT NULL,
    `event_id` INT UNSIGNED NOT NULL,
    `status` ENUM('Confirmed', 'Waitlisted', 'Cancelled') NOT NULL DEFAULT 'Confirmed',
    `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_registrations_student`
        FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_registrations_event`
        FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `uq_student_event` UNIQUE (`student_id`, `event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Sample Seed Data
-- ---------------------------------------------------------------------

-- Seed Students
INSERT INTO `students` (`full_name`, `email`, `mobile`, `password_hash`, `course`, `academic_year`, `gender`) VALUES
('Prey Patel', 'prey.patel@studenthub.edu', '9876543210', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFvC3zS3f8yJq2Z9m7X9uA6v8W1tQxKy', 'Computer Engineering', '2nd Year', 'Male'),
('Aarav Shah', 'aarav.shah@studenthub.edu', '9898989898', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFvC3zS3f8yJq2Z9m7X9uA6v8W1tQxKy', 'Information Technology', '3rd Year', 'Male'),
('Diya Sharma', 'diya.sharma@studenthub.edu', '9123456780', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFvC3zS3f8yJq2Z9m7X9uA6v8W1tQxKy', 'Computer Science', '1st Year', 'Female');

-- Seed Events
INSERT INTO `events` (`title`, `category`, `event_date`, `venue`, `max_seats`) VALUES
('Web Development Hackathon 2026', 'Technical', '2026-10-15', 'Main Computer Lab', 60),
('Cloud & DevOps Bootcamp', 'Workshop', '2026-10-22', 'Seminar Hall B', 45),
('Tech Career Fair & Mock Interviews', 'Career', '2026-11-05', 'Auditorium 1', 100),
('Annual Cultural Fest - Resonance', 'Cultural', '2026-11-20', 'College Open Ground', 250);

-- Seed Registrations
INSERT INTO `registrations` (`student_id`, `event_id`, `status`) VALUES
(1, 1, 'Confirmed'),
(1, 2, 'Confirmed'),
(2, 1, 'Confirmed'),
(3, 3, 'Confirmed');
