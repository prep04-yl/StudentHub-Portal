-- Schema for Practical 14: RBAC, Dynamic Menu, and Audit Log
CREATE DATABASE IF NOT EXISTS studenthub_db;
USE studenthub_db;

-- 1. Users table with roles
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'faculty', 'student') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Audit logs table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    username VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed users (password is 'password123' hashed with BCRYPT)
-- Admin: admin / password123
-- Faculty: faculty / password123
-- Student: student / password123
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('faculty', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty'),
('student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student')
ON DUPLICATE KEY UPDATE username=username;

-- Seed initial audit logs
INSERT INTO audit_logs (username, action, details, ip_address) VALUES
('admin', 'LOGIN', 'Admin logged into portal', '127.0.0.1'),
('admin', 'CREATE_STUDENT', 'Added new student CS101', '127.0.0.1'),
('faculty', 'VIEW_EVENTS', 'Faculty viewed event list', '127.0.0.1');
