<?php
/**
 * Simple DB config helper to match the sample architecture (BookController)
 * Guard class declaration so including `config.php` multiple times won't cause
 * a fatal "Cannot declare class config" error in development.
 */
if (!class_exists('config')) {
    class config {
    private static $dbHost = 'localhost';
    private static $dbName = 'edumind';
    private static $dbUser = 'root';
    private static $dbPass = '';

    public static function getConnexion() {
        try {
            $pdo = new PDO("mysql:host=" . self::$dbHost . ";dbname=" . self::$dbName . ";charset=utf8mb4", self::$dbUser, self::$dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die('Connection error: ' . $e->getMessage());
        }
    }
    }
}

?>