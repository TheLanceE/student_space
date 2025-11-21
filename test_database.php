<?php
/**
 * Test Database Connection
 * Run: php test_database.php
 */

require_once 'config/database.php';

echo "=== Testing Database Connection ===\n\n";

try {
    $conn = getDBConnection();
    echo "✓ Database connection successful!\n";
    echo "✓ Connected to: " . DB_NAME . "\n\n";
    
    // Check if reports table exists and count records
    $result = $conn->query("SELECT COUNT(*) as count FROM reports");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✓ Reports table exists\n";
        echo "✓ Number of reports: " . $row['count'] . "\n\n";
        
        // Show sample data
        if ($row['count'] > 0) {
            echo "Sample reports:\n";
            $result = $conn->query("SELECT id, student, type, status FROM reports LIMIT 5");
            while ($report = $result->fetch_assoc()) {
                echo "  - ID: {$report['id']}, Student: {$report['student']}, Type: {$report['type']}, Status: {$report['status']}\n";
            }
        }
    }
    
    closeDBConnection($conn);
    echo "\n=== Database is ready! ===\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nPlease check:\n";
    echo "1. MySQL/MariaDB is running\n";
    echo "2. Database credentials in config/database.php are correct\n";
    echo "3. Run setup_database.php if you haven't already\n";
}

