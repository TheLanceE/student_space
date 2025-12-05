<?php
require_once __DIR__ . '/Controllers/config.php';

echo "<h1>Teacher Account Check</h1>";

try {
    $stmt = $db_connection->prepare("SELECT id, username, fullName, email, createdAt FROM teachers WHERE deleted_at IS NULL ORDER BY id");
    $stmt->execute();
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Found " . count($teachers) . " teacher(s)</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Created</th></tr>";
    
    foreach ($teachers as $teacher) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($teacher['id']) . "</td>";
        echo "<td>" . htmlspecialchars($teacher['username']) . "</td>";
        echo "<td>" . htmlspecialchars($teacher['fullName']) . "</td>";
        echo "<td>" . htmlspecialchars($teacher['email']) . "</td>";
        echo "<td>" . htmlspecialchars($teacher['createdAt']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if teacher_jane specifically exists
    $stmt2 = $db_connection->prepare("SELECT * FROM teachers WHERE username = 'teacher_jane' AND deleted_at IS NULL");
    $stmt2->execute();
    $jane = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Teacher Jane Check</h2>";
    if ($jane) {
        echo "<p style='color: green;'>✓ teacher_jane EXISTS in database</p>";
        echo "<p>Full Name: " . htmlspecialchars($jane['fullName']) . "</p>";
        echo "<p>Email: " . htmlspecialchars($jane['email']) . "</p>";
        echo "<p>Password Hash: " . substr($jane['password'], 0, 20) . "...</p>";
        
        // Test password verification
        $testPassword = 'password123';
        $hashVerify = password_verify($testPassword, $jane['password']);
        $directMatch = ($testPassword === $jane['password']);
        
        echo "<h3>Password Test ('password123')</h3>";
        echo "<p>Hashed match: " . ($hashVerify ? "✓ YES" : "✗ NO") . "</p>";
        echo "<p>Direct match: " . ($directMatch ? "✓ YES" : "✗ NO") . "</p>";
        
    } else {
        echo "<p style='color: red;'>✗ teacher_jane NOT FOUND in database</p>";
        echo "<p><strong>Action needed:</strong> Run database.sql to insert teacher accounts</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
