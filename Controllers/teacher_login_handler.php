<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Views/teacher-back-office/login.php');
    exit;
}

// CSRF validation
$csrf = $_POST['csrf_token'] ?? '';
if (!SessionManager::validateCSRFToken($csrf)) {
    header('Location: ../Views/teacher-back-office/login.php?error=csrf');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: ../Views/teacher-back-office/login.php?error=empty');
    exit;
}

try {
    $stmt = $db_connection->prepare("SELECT * FROM teachers WHERE username = ? AND (deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')");
    $stmt->execute([$username]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    $passwordValid = $teacher && password_verify($password, $teacher['password']);

    if ($passwordValid) {
        $updateStmt = $db_connection->prepare("UPDATE teachers SET lastLoginAt = NOW() WHERE id = ?");
        $updateStmt->execute([$teacher['id']]);

        // Create secure session and regenerate ID
        SessionManager::createUserSession($teacher['id'], $teacher['username'], 'teacher', $teacher['fullName'] ?? $teacher['username']);
        $_SESSION['email'] = $teacher['email'] ?? '';
        $_SESSION['role'] = 'teacher';
        $_SESSION['logged_in'] = true;

        header('Location: ../Views/teacher-back-office/dashboard.php');
        exit;
    }

    header('Location: ../Views/teacher-back-office/login.php?error=invalid');
    exit;

} catch (PDOException $e) {
    error_log('[Teacher Login] DB error: ' . $e->getMessage());
    header('Location: ../Views/teacher-back-office/login.php?error=db');
    exit;
}
?>
