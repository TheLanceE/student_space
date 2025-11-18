<?php
// Database configuration
$db_config = [
    'host' => 'localhost',
    'dbname' => 'edumind',
    'username' => 'root',
    'password' => ''
];

// Try to connect to database, fallback to no-db mode if fails
try {
    $db_connection = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']}", 
        $db_config['username'], 
        $db_config['password']
    );
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    define('DB_AVAILABLE', true);
} catch (PDOException $e) {
    $db_connection = null;
    define('DB_AVAILABLE', false);
    // Application will use localStorage fallback
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
