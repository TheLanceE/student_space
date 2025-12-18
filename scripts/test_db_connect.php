<?php
require_once __DIR__ . '/../config.php';
try {
    $db = config::getConnexion();
    echo "CONNECTED\n";
    $stmt = $db->query("SELECT DATABASE() as db");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    var_export($row);
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
