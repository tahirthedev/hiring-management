<?php
/**
 * WordPress Developer Screening System - Installer
 * Run this once to set up the database.
 */

$host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
$dbName = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'wp_screening';
$port = getenv('MYSQLPORT') ?: '3306';

echo "<h2>WordPress Screening System - Installer</h2>";

try {
    // Connect without database
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>&#10004; Database '$dbName' created.</p>";

    $pdo->exec("USE `$dbName`");

    // Admin users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p>&#10004; Table 'admin_users' created.</p>";

    // Applicants table
    $pdo->exec("CREATE TABLE IF NOT EXISTS applicants (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p>&#10004; Table 'applicants' created.</p>";

    // Screening answers table
    $pdo->exec("CREATE TABLE IF NOT EXISTS screening_answers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        applicant_id INT NOT NULL,
        question_number INT NOT NULL,
        answer TEXT NOT NULL,
        score INT DEFAULT 0,
        matched_keywords TEXT DEFAULT NULL,
        FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE,
        UNIQUE KEY unique_answer (applicant_id, question_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p>&#10004; Table 'screening_answers' created.</p>";

    // Seed admin user
    $adminPassword = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES ('admin', ?) ON DUPLICATE KEY UPDATE username = username");
    $stmt->execute([$adminPassword]);
    echo "<p>&#10004; Admin user seeded (admin / admin123).</p>";

    // Check uploads directory
    $uploadDir = __DIR__ . '/uploads/cv/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    echo "<p>&#10004; Uploads directory ready.</p>";

    echo "<hr>";
    echo "<h3 style='color: green;'>Installation Complete!</h3>";
    echo "<p><strong>Admin Login:</strong> <a href='/admin/login.php'>/admin/login.php</a></p>";
    echo "<p><strong>Application Form:</strong> <a href='/'>/</a></p>";
    echo "<p style='color: red;'><strong>IMPORTANT:</strong> Delete this install.php file after setup!</p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>&#10008; Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your MySQL credentials in this file and in config/database.php</p>";
}
