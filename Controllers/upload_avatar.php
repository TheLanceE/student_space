<?php
require_once __DIR__ . '/config.php';

// Require login
$userId = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'student';
if (!$userId) {
    header('Location: ../Views/front-office/login.php?error=not_logged_in');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['avatar'])) {
    header('Location: ../Views/front-office/profile.php?error=avatar_missing');
    exit;
}

$file = $_FILES['avatar'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    header('Location: ../Views/front-office/profile.php?error=avatar_upload');
    exit;
}

// Validate size (max 2MB)
$maxSize = 2 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    header('Location: ../Views/front-office/profile.php?error=avatar_size');
    exit;
}

// Validate MIME
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

if (!isset($allowed[$mime])) {
    header('Location: ../Views/front-office/profile.php?error=avatar_type');
    exit;
}

$ext = $allowed[$mime];
$uploadDir = realpath(__DIR__ . '/../uploads/avatars');
if (!$uploadDir) {
    $uploadDir = __DIR__ . '/../uploads/avatars';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
}

// Clean old avatars for this user
$pattern = $uploadDir . '/' . $userId . '.*';
foreach (glob($pattern) as $old) {
    @unlink($old);
}

$filename = $userId . '.' . $ext;
$targetPath = $uploadDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    header('Location: ../Views/front-office/profile.php?error=avatar_save');
    exit;
}

$relativePath = 'uploads/avatars/' . $filename;
$_SESSION['avatar_path'] = $relativePath;

// Persist to DB if column exists
try {
    $table = $role === 'teacher' ? 'teachers' : 'students';
    $checkSql = "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'avatarPath'";
    $checkStmt = $db_connection->prepare($checkSql);
    $checkStmt->execute([$table]);
    $hasColumn = $checkStmt->fetchColumn() > 0;
    if ($hasColumn) {
        $upd = $db_connection->prepare("UPDATE {$table} SET avatarPath = ? WHERE id = ?");
        $upd->execute([$relativePath, $userId]);
    }
} catch (Exception $e) {
    error_log('[Avatar] Persist warning: ' . $e->getMessage());
}

header('Location: ../Views/front-office/profile.php?success=avatar');
exit;
