-- Practical 12 Database Schema: Events Management with Poster
CREATE DATABASE IF NOT EXISTS studenthub_db;
USE studenthub_db;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(100) NOT NULL,
    poster VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Demo records
INSERT INTO events (title, category, event_date, venue, poster) VALUES
('Tech Fusion Hackathon 2026', 'Technical', '2026-11-15', 'Auditorium Hall A', NULL),
('Inter-College Cricket Cup', 'Sports', '2026-11-20', 'University Sports Ground', NULL),
('Cultural Fest - Tarang', 'Cultural', '2026-12-05', 'Main Amphitheatre', NULL);
