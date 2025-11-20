<?php
if (session_status() === PHP_SESSION_NONE) session_start();

define('DB_AVAILABLE', true);
define('DB_HOST', 'localhost');
define('DB_NAME', 'edumind');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    $pdo = null;
    define('DB_AVAILABLE', false);
    error_log("Database connection failed: " . $e->getMessage());
}
?>

