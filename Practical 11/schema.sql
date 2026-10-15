-- Database Setup for Practical 11: Student Management CRUD
CREATE DATABASE IF NOT EXISTS studenthub_db;
USE studenthub_db;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_no VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample Demo Data
INSERT INTO students (roll_no, name, email, department, semester) VALUES
('CS101', 'Alice Johnson', 'alice@example.com', 'Computer Science', 4),
('IT102', 'Bob Smith', 'bob@example.com', 'Information Technology', 6),
('EC103', 'Charlie Brown', 'charlie@example.com', 'Electronics', 2),
('CS104', 'Diana Prince', 'diana@example.com', 'Computer Science', 6),
('ME105', 'Evan Wright', 'evan@example.com', 'Mechanical', 4);
