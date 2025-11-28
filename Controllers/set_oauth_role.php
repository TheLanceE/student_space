<?php
/**
 * Helper to set OAuth role before redirect
 * Called by Google Sign-In button
 */

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$role = $input['role'] ?? 'student';

if (!in_array($role, ['student', 'teacher', 'admin'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid role']);
    exit;
}

$_SESSION['oauth_role'] = $role;

echo json_encode(['success' => true, 'role' => $role]);
?>
