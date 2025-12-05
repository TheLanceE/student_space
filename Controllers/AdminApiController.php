<?php
/**
 * REST API Controller for Admin Operations
 * Handles bulk operations for users, courses, events, quizzes
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_check.php';

// Ensure admin role
if ($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get request method and endpoint
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? $_GET['path'] ?? '/';

// Debug logging
error_log("AdminApiController: method=$method, path=$path");

// CSRF protection
function validate_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        if ($token && !hash_equals($_SESSION['csrf_token'], $token)) {
            error_log("CSRF validation failed: expected={$_SESSION['csrf_token']}, got=$token");
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit;
        }
    }
}

validate_csrf();

// Parse JSON input for non-GET requests
$input = null;
if ($method !== 'GET') {
    $input = json_decode(file_get_contents('php://input'), true);
}

/**
 * USERS API
 */
if (preg_match('#^/users/?$#', $path) && $method === 'GET') {
    // List all users (students, teachers, admins)
    $students = $db_connection->query("SELECT id, username, fullName, email, gradeLevel, createdAt, lastLoginAt, 'student' as role FROM students WHERE deleted_at IS NULL")->fetchAll();
    $teachers = $db_connection->query("SELECT id, username, fullName, email, specialty, createdAt, lastLoginAt, 'teacher' as role FROM teachers WHERE deleted_at IS NULL")->fetchAll();
    $admins = $db_connection->query("SELECT id, username, name as fullName, NULL as email, NULL as extra, createdAt, lastLoginAt, 'admin' as role FROM admins WHERE deleted_at IS NULL")->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'students' => $students,
            'teachers' => $teachers,
            'admins' => $admins
        ]
    ]);
    exit;
}

if (preg_match('#^/users/bulk-delete/?$#', $path) && $method === 'POST') {
    // Bulk delete users
    $ids = $input['ids'] ?? [];
    $role = $input['role'] ?? '';
    
    error_log("Bulk delete: role=$role, ids=" . json_encode($ids));
    
    if (empty($ids) || !in_array($role, ['student', 'teacher', 'admin'])) {
        http_response_code(400);
        error_log("Invalid input for bulk delete");
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }
    
    // Prevent deleting current admin
    if ($role === 'admin' && in_array($_SESSION['user_id'], $ids)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Cannot delete current admin']);
        exit;
    }
    
    // Soft delete
    $table = $role . 's';
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db_connection->prepare("UPDATE $table SET deleted_at = NOW() WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    
    error_log("Deleted " . $stmt->rowCount() . " users from $table");
    
    echo json_encode([
        'success' => true,
        'deleted' => $stmt->rowCount()
    ]);
    exit;
}

if (preg_match('#^/users/detect-fake/?$#', $path) && $method === 'GET') {
    // Detect potential fake accounts
    // Criteria: No last login within 90 days, no email, generic usernames
    $fake_students = $db_connection->query("
        SELECT id, username, fullName, email, createdAt, lastLoginAt 
        FROM students 
        WHERE deleted_at IS NULL 
        AND (
            lastLoginAt IS NULL 
            OR lastLoginAt < DATE_SUB(NOW(), INTERVAL 90 DAY)
            OR email IS NULL 
            OR email = ''
            OR username LIKE 'user%'
            OR username LIKE 'test%'
        )
    ")->fetchAll();
    
    echo json_encode([
        'success' => true,
        'fake_accounts' => $fake_students,
        'count' => count($fake_students)
    ]);
    exit;
}

/**
 * COURSES API
 */
if (preg_match('#^/courses/?$#', $path) && $method === 'GET') {
    // List all courses
    $courses = $db_connection->query("
        SELECT c.*, t.fullName as teacherName 
        FROM courses c 
        LEFT JOIN teachers t ON c.teacherId = t.id 
        WHERE c.deleted_at IS NULL
        ORDER BY c.createdAt DESC
    ")->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $courses]);
    exit;
}

if (preg_match('#^/courses/bulk-delete/?$#', $path) && $method === 'POST') {
    // Bulk delete courses
    $ids = $input['ids'] ?? [];
    
    if (empty($ids)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No IDs provided']);
        exit;
    }
    
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db_connection->prepare("UPDATE courses SET deleted_at = NOW() WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    
    echo json_encode([
        'success' => true,
        'deleted' => $stmt->rowCount()
    ]);
    exit;
}

/**
 * EVENTS API
 */
if (preg_match('#^/events/?$#', $path) && $method === 'GET') {
    // List all events
    $events = $db_connection->query("
        SELECT e.*, t.fullName as teacherName 
        FROM events e 
        LEFT JOIN teachers t ON e.teacherId = t.id 
        WHERE e.deleted_at IS NULL
        ORDER BY e.date DESC, e.startTime DESC
    ")->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $events]);
    exit;
}

if (preg_match('#^/events/bulk-delete/?$#', $path) && $method === 'POST') {
    // Bulk delete events
    $ids = $input['ids'] ?? [];
    
    if (empty($ids)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No IDs provided']);
        exit;
    }
    
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db_connection->prepare("UPDATE events SET deleted_at = NOW() WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    
    echo json_encode([
        'success' => true,
        'deleted' => $stmt->rowCount()
    ]);
    exit;
}

/**
 * QUIZZES API
 */
if (preg_match('#^/quizzes/?$#', $path) && $method === 'GET') {
    // List all quizzes
    $quizzes = $db_connection->query("
        SELECT q.*, c.title as courseName, t.fullName as teacherName 
        FROM quizzes q 
        LEFT JOIN courses c ON q.courseId = c.id 
        LEFT JOIN teachers t ON q.createdBy = t.id 
        WHERE q.deleted_at IS NULL
        ORDER BY q.createdAt DESC
    ")->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $quizzes]);
    exit;
}

if (preg_match('#^/quizzes/bulk-delete/?$#', $path) && $method === 'POST') {
    // Bulk delete quizzes
    $ids = $input['ids'] ?? [];
    
    if (empty($ids)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No IDs provided']);
        exit;
    }
    
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db_connection->prepare("UPDATE quizzes SET deleted_at = NOW() WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    
    echo json_encode([
        'success' => true,
        'deleted' => $stmt->rowCount()
    ]);
    exit;
}

// 404 for unknown endpoints
error_log("AdminApiController: 404 - method=$method, path=$path, query_string=" . ($_SERVER['QUERY_STRING'] ?? ''));
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => 'Endpoint not found',
    'debug' => ['method' => $method, 'path' => $path, 'available_endpoints' => ['/users/bulk-delete', '/users', '/courses', '/events', '/quizzes']]
]);
?>
