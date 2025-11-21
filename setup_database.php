<?php
/**
 * Database Setup Script
 * Run this file once to create the database and table
 * Usage: php setup_database.php
 */

require_once 'config/database.php';

echo "=== EduMind Database Setup ===\n\n";

// Connect without selecting database first
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error . "\n");
    }
    
    echo "✓ Connected to MySQL server\n";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "✓ Database '" . DB_NAME . "' created or already exists\n";
    } else {
        die("Error creating database: " . $conn->error . "\n");
    }
    
    // Select database
    $conn->select_db(DB_NAME);
    echo "✓ Selected database '" . DB_NAME . "'\n";
    
    // Create reports table
    $sql = "CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student VARCHAR(255) NOT NULL,
        quiz VARCHAR(255) DEFAULT NULL,
        type VARCHAR(100) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Pending',
        content TEXT NOT NULL,
        created_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_date DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_student (student),
        INDEX idx_type (type),
        INDEX idx_created_date (created_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "✓ Table 'reports' created or already exists\n";
    } else {
        die("Error creating table: " . $conn->error . "\n");
    }
    
    // Check if table is empty and insert sample data
    $result = $conn->query("SELECT COUNT(*) as count FROM reports");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        echo "\nInserting sample data...\n";
        $sampleData = [
            ['John Doe', 'Math Quiz 1', 'Performance', 'Pending', 'Review'],
            ['Alex Johnson', NULL, 'Performance', 'Pending', 'PERFORMANCE SUMMARY REPORT Generated on: 2025-11-11. This report contains detailed analysis of student performance metrics.'],
            ['Alex Johnson', NULL, 'Performance', 'Pending', 'PERFORMANCE SUMMARY REPORT Generated on: 2025-11-11. This report contains detailed analysis of student performance metrics.']
        ];
        
        $stmt = $conn->prepare("INSERT INTO reports (student, quiz, type, status, content, created_date) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($sampleData as $data) {
            $createdDate = date('Y-m-d H:i:s', strtotime('-' . rand(1, 10) . ' hours'));
            $stmt->bind_param("ssssss", $data[0], $data[1], $data[2], $data[3], $data[4], $createdDate);
            $stmt->execute();
        }
        
        $stmt->close();
        echo "✓ Sample data inserted\n";
    } else {
        echo "✓ Table already contains data (" . $row['count'] . " records)\n";
    }
    
    $conn->close();
    
    echo "\n=== Setup Complete! ===\n";
    echo "You can now access the application at: http://localhost:8000/View/Back.html\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nPlease check your database configuration in config/database.php\n";
}

