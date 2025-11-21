<?php
/**
 * Database Configuration
 * Update these values according to your MySQL server settings
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'edumind_db');

/**
 * Create database connection
 */
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8mb4 for proper character encoding
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        die(json_encode([
            'success' => false,
            'message' => 'Database connection error: ' . $e->getMessage()
        ]));
    }
}

/**
 * Close database connection
 */
function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}

