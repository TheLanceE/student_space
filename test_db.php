<?php
// Simple DB connection test for your XAMPP project
// Place this file in the project root and open it in your browser:
// http://localhost/my%20work%20quizzes%20for%20web/test_db.php

require_once __DIR__ . '/config.php';

try {
    $db = config::getConnexion();
    echo "<h2>Database connection: SUCCESS</h2>";
    // Show server version and PDO attributes
    echo "<p>PDO driver: " . $db->getAttribute(PDO::ATTR_DRIVER_NAME) . "</p>";
    echo "<p>Server info: " . htmlentities($db->getAttribute(PDO::ATTR_SERVER_INFO) ?: 'n/a') . "</p>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Database connection: FAILED</h2>";
    echo "<pre>\n" . htmlentities($e->getMessage()) . "\n</pre>";
}
