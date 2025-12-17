<?php
require_once __DIR__ . '/config.php';

// Session already started by config.php via SessionManager

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  error_log('[OAuth Onboard] Invalid method: ' . $_SERVER['REQUEST_METHOD']);
  http_response_code(405);
  die('Method not allowed');
}

// CSRF validation
$csrf = $_POST['csrf_token'] ?? '';
if (!SessionManager::validateCSRFToken($csrf)) {
    error_log('[OAuth Onboard] CSRF validation failed');
    header('Location: ../Views/front-office/login.php?error=csrf');
    exit;
}

$role = $_POST['role'] ?? ($_SESSION['role'] ?? 'student');
$userId = $_SESSION['user_id'] ?? null;

// Validate role against whitelist
$validRoles = ['student', 'teacher'];
if (!in_array($role, $validRoles, true)) {
    $role = 'student';
}

error_log('[OAuth Onboard] POST data: ' . json_encode($_POST));
error_log('[OAuth Onboard] Session data: user_id=' . $userId . ', role=' . $role);

if (!$userId) {
  error_log('[OAuth Onboard] ERROR: No user_id in session');
  header('Location: ../Views/front-office/login.php?error=session_missing');
  exit;
}

// Collect fields
$fullName = $_POST['fullName'] ?? '';
$email = $_POST['email'] ?? '';
$mobile = $_POST['mobile'] ?? null;
$address = $_POST['address'] ?? null;
$gradeLevel = $_POST['gradeLevel'] ?? null;
$specialty = $_POST['specialty'] ?? ($_POST['department'] ?? null); // support legacy department field

try {
  if ($role === 'student') {
    $stmt = $db_connection->prepare("UPDATE students SET fullName = ?, email = ?, mobile = ?, address = ?, gradeLevel = ? WHERE id = ?");
    $stmt->execute([$fullName, $email, $mobile, $address, $gradeLevel, $userId]);
    header('Location: ../Views/front-office/dashboard.php?onboard=1');
  } elseif ($role === 'teacher') {
    $stmt = $db_connection->prepare("UPDATE teachers SET fullName = ?, email = ?, mobile = ?, address = ?, specialty = ? WHERE id = ?");
    $stmt->execute([$fullName, $email, $mobile, $address, $specialty, $userId]);
    header('Location: ../Views/teacher-back-office/dashboard.php?onboard=1');
  } else {
    header('Location: ../Views/admin-back-office/login.php?error=oauth_admin_disabled');
  }
  exit;
} catch (Exception $e) {
  error_log('[OAuth Onboard] Update error: ' . $e->getMessage());
  header('Location: ../Views/front-office/login.php?error=onboard_failed');
  exit;
}
