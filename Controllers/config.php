<?php
/**
 * Database and Session Configuration
 * Central configuration file for EduMind+ application
 */

// Load SessionManager
require_once __DIR__ . '/SessionManager.php';

// Database configuration
$db_config = [
    'host' => 'localhost',
    'dbname' => 'edumind',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// Connect to database (required - no fallback)
try {
    $db_connection = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}", 
        $db_config['username'], 
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    
    define('DB_AVAILABLE', true);
    
} catch (PDOException $e) {
    // Database connection is required - no localStorage fallback
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error' => 'Database connection failed. Please contact administrator.'
    ]));
}

// Initialize secure session management
SessionManager::init();

// Set secure headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// For API endpoints, set JSON content type
if (strpos($_SERVER['REQUEST_URI'] ?? '', 'Controller.php') !== false) {
    header('Content-Type: application/json');
}
?>

