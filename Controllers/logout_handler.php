<?php
/**
 * Simple Logout Handler
 */

require_once __DIR__ . '/SessionManager.php';

// Initialize session to read role before destroying
SessionManager::init();
$user = SessionManager::getCurrentUser();
$role = $user['role'] ?? null;

// Destroy session
SessionManager::destroy();

// Redirect to the correct login based on role
switch ($role) {
	case 'teacher':
		header('Location: ../Views/teacher-back-office/login.php');
		break;
	case 'admin':
		header('Location: ../Views/admin-back-office/login.php');
		break;
	default:
		header('Location: ../Views/front-office/login.php');
		break;
}
exit;
?>