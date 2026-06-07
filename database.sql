-- WordPress Developer Screening System
-- Database Schema

CREATE DATABASE IF NOT EXISTS wp_screening;
USE wp_screening;

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Applicants table
CREATE TABLE IF NOT EXISTS applicants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    city VARCHAR(50) NOT NULL,
    area VARCHAR(100) NOT NULL,
    wp_experience INT NOT NULL,
    employment_status VARCHAR(50) NOT NULL,
    expected_salary INT NOT NULL,
    portfolio_url VARCHAR(255) DEFAULT NULL,
    linkedin_url VARCHAR(255) DEFAULT NULL,
    cv_filename VARCHAR(255) DEFAULT NULL,
    total_score INT DEFAULT 0,
    status ENUM('Pending','Shortlisted','Manual Review','Rejected') DEFAULT 'Pending',
    rejection_reason VARCHAR(255) DEFAULT NULL,
    admin_notes TEXT DEFAULT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_score (total_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Screening answers table
CREATE TABLE IF NOT EXISTS screening_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_id INT NOT NULL,
    question_number INT NOT NULL,
    answer TEXT NOT NULL,
    score INT DEFAULT 0,
    matched_keywords TEXT DEFAULT NULL,
    FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_answer (applicant_id, question_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed admin account (password: admin123)
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$Y5JceI4REHVfRDxd0QtxMeRQv1TE7ZTmdeCCV7Lnd1asmG2nJ1Sx2')
ON DUPLICATE KEY UPDATE username = username;
