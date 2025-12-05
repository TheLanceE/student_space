<?php
/**
 * System Diagnostic and Test Page
 * Run this to verify all systems are working correctly
 */

// Suppress all errors for clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'tests' => [],
    'summary' => []
];

// ==============================================
// TEST 1: Database Connection
// ==============================================
$test = 'Database Connection';
try {
    $pdo = new PDO('mysql:host=localhost;dbname=edumind;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $results['tests'][$test] = ['status' => 'PASS', 'message' => 'Connected successfully'];
} catch (Exception $e) {
    $results['tests'][$test] = ['status' => 'FAIL', 'message' => $e->getMessage()];
}

// ==============================================
// TEST 2: Session Management
// ==============================================
$test = 'Session Management';
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['test_key'] = 'test_value';
    $testResult = $_SESSION['test_key'] === 'test_value';
    unset($_SESSION['test_key']);
    $results['tests'][$test] = ['status' => $testResult ? 'PASS' : 'FAIL', 'message' => $testResult ? 'Sessions working' : 'Session data not persisting'];
} catch (Exception $e) {
    $results['tests'][$test] = ['status' => 'FAIL', 'message' => $e->getMessage()];
}

// ==============================================
// TEST 3: Table Existence
// ==============================================
$test = 'Required Tables';
$requiredTables = ['admins', 'students', 'teachers', 'courses', 'events', 'quizzes', 'projects'];
$missingTables = [];
try {
    $stmt = $pdo->query("SHOW TABLES");
    $existingTables = array_map('current', $stmt->fetchAll());
    foreach ($requiredTables as $table) {
        if (!in_array($table, $existingTables)) {
            $missingTables[] = $table;
        }
    }
    $results['tests'][$test] = [
        'status' => empty($missingTables) ? 'PASS' : 'FAIL',
        'message' => empty($missingTables) ? 'All tables exist' : 'Missing: ' . implode(', ', $missingTables),
        'details' => ['existing' => $existingTables]
    ];
} catch (Exception $e) {
    $results['tests'][$test] = ['status' => 'FAIL', 'message' => $e->getMessage()];
}

// ==============================================
// TEST 4: deleted_at Columns
// ==============================================
$test = 'Soft Delete Columns';
$missingDeletedAt = [];
try {
    $checkTables = ['admins', 'students', 'teachers', 'courses', 'events', 'quizzes'];
    foreach ($checkTables as $table) {
        $stmt = $pdo->query("SHOW COLUMNS FROM $table LIKE 'deleted_at'");
        if ($stmt->rowCount() === 0) {
            $missingDeletedAt[] = $table;
        }
    }
    $results['tests'][$test] = [
        'status' => empty($missingDeletedAt) ? 'PASS' : 'FAIL',
        'message' => empty($missingDeletedAt) ? 'All tables have deleted_at' : 'Missing in: ' . implode(', ', $missingDeletedAt)
    ];
} catch (Exception $e) {
    $results['tests'][$test] = ['status' => 'FAIL', 'message' => $e->getMessage()];
}

// ==============================================
// TEST 5: Google OAuth Columns
// ==============================================
$test = 'OAuth Columns';
$missingGoogleId = [];
try {
    $checkTables = ['students', 'teachers'];
    foreach ($checkTables as $table) {
        $stmt = $pdo->query("SHOW COLUMNS FROM $table LIKE 'google_id'");
        if ($stmt->rowCount() === 0) {
            $missingGoogleId[] = $table;
        }
    }
    $results['tests'][$test] = [
        'status' => empty($missingGoogleId) ? 'PASS' : 'FAIL',
        'message' => empty($missingGoogleId) ? 'OAuth columns present' : 'Missing google_id in: ' . implode(', ', $missingGoogleId)
    ];
} catch (Exception $e) {
    $results['tests'][$test] = ['status' => 'FAIL', 'message' => $e->getMessage()];
}

// ==============================================
// TEST 6: User Counts
// ==============================================
$test = 'User Data';
try {
    $adminCount = $pdo->query("SELECT COUNT(*) FROM admins WHERE deleted_at IS NULL")->fetchColumn();
    $studentCount = $pdo->query("SELECT COUNT(*) FROM students WHERE deleted_at IS NULL")->fetchColumn();
    $teacherCount = $pdo->query("SELECT COUNT(*) FROM teachers WHERE deleted_at IS NULL")->fetchColumn();
    
    $results['tests'][$test] = [
        'status' => $adminCount > 0 ? 'PASS' : 'WARN',
        'message' => "Admins: $adminCount, Students: $studentCount, Teachers: $teacherCount",
        'details' => [
            'admins' => $adminCount,
            'students' => $studentCount,
            'teachers' => $teacherCount
        ]
    ];
} catch (Exception $e) {
    $results['tests'][$test] = ['status' => 'FAIL', 'message' => $e->getMessage()];
}

// ==============================================
// TEST 7: Config Files
// ==============================================
$test = 'Config Files';
$configFiles = [
    'Controllers/config.php',
    'Controllers/SessionManager.php',
    'Controllers/GoogleOAuthHandler.php',
    'Controllers/AdminApiController.php',
    'Models/User.php'
];
$missingFiles = [];
foreach ($configFiles as $file) {
    if (!file_exists(__DIR__ . '/' . $file)) {
        $missingFiles[] = $file;
    }
}
$results['tests'][$test] = [
    'status' => empty($missingFiles) ? 'PASS' : 'FAIL',
    'message' => empty($missingFiles) ? 'All config files present' : 'Missing: ' . implode(', ', $missingFiles)
];

// ==============================================
// TEST 8: OAuth Configuration
// ==============================================
$test = 'OAuth Config';
$oauthConfigFile = __DIR__ . '/Controllers/oauth_config.local.php';
if (file_exists($oauthConfigFile)) {
    require_once $oauthConfigFile;
    $hasClientId = defined('GOOGLE_CLIENT_ID') && !empty(GOOGLE_CLIENT_ID);
    $hasSecret = defined('GOOGLE_CLIENT_SECRET') && !empty(GOOGLE_CLIENT_SECRET);
    $hasRedirect = defined('GOOGLE_REDIRECT_URI') && !empty(GOOGLE_REDIRECT_URI);
    
    $results['tests'][$test] = [
        'status' => ($hasClientId && $hasSecret && $hasRedirect) ? 'PASS' : 'WARN',
        'message' => 'Config file exists',
        'details' => [
            'client_id' => $hasClientId ? 'SET' : 'NOT SET',
            'client_secret' => $hasSecret ? 'SET' : 'NOT SET',
            'redirect_uri' => $hasRedirect ? 'SET' : 'NOT SET'
        ]
    ];
} else {
    $results['tests'][$test] = [
        'status' => 'WARN',
        'message' => 'oauth_config.local.php not found (OAuth disabled)'
    ];
}

// ==============================================
// TEST 9: Admin API Endpoint
// ==============================================
$test = 'Admin API';
try {
    $apiFile = __DIR__ . '/Controllers/AdminApiController.php';
    if (file_exists($apiFile)) {
        $content = file_get_contents($apiFile);
        $hasUserEndpoint = strpos($content, '/users/bulk-delete') !== false;
        $results['tests'][$test] = [
            'status' => $hasUserEndpoint ? 'PASS' : 'FAIL',
            'message' => $hasUserEndpoint ? 'Bulk delete endpoint exists' : 'Missing bulk delete endpoint'
        ];
    } else {
        $results['tests'][$test] = ['status' => 'FAIL', 'message' => 'AdminApiController.php not found'];
    }
} catch (Exception $e) {
    $results['tests'][$test] = ['status' => 'FAIL', 'message' => $e->getMessage()];
}

// ==============================================
// TEST 10: PHP Extensions
// ==============================================
$test = 'PHP Extensions';
$requiredExtensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring'];
$missingExtensions = [];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}
$results['tests'][$test] = [
    'status' => empty($missingExtensions) ? 'PASS' : 'FAIL',
    'message' => empty($missingExtensions) ? 'All extensions loaded' : 'Missing: ' . implode(', ', $missingExtensions)
];

// ==============================================
// Summary
// ==============================================
$passCount = 0;
$failCount = 0;
$warnCount = 0;

foreach ($results['tests'] as $testResult) {
    if ($testResult['status'] === 'PASS') $passCount++;
    elseif ($testResult['status'] === 'FAIL') $failCount++;
    elseif ($testResult['status'] === 'WARN') $warnCount++;
}

$results['summary'] = [
    'total' => count($results['tests']),
    'passed' => $passCount,
    'failed' => $failCount,
    'warnings' => $warnCount,
    'status' => $failCount > 0 ? 'SYSTEM NEEDS ATTENTION' : ($warnCount > 0 ? 'SYSTEM OK (with warnings)' : 'ALL SYSTEMS GO')
];

// ==============================================
// Output
// ==============================================
echo json_encode($results, JSON_PRETTY_PRINT);
?>
