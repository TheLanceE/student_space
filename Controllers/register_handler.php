<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Views/front-office/register.php');
    exit;
}

function redirect_with_error($msg) {
    $_SESSION['error'] = $msg;
    header('Location: ../Views/front-office/login.php');
    exit;
}

// CSRF validation
$csrf = $_POST['csrf_token'] ?? '';
if (!SessionManager::validateCSRFToken($csrf)) {
    redirect_with_error('Invalid session token. Please try again.');
}

$role = $_POST['role'] ?? 'student';

if ($role === 'teacher') {
    $required = ['username','password','fullName','email','mobile','address','subject','nationalId'];
} else {
    $role = 'student';
    $required = ['username','password','fullName','email','mobile','address','gradeLevel'];
}

foreach ($required as $key) {
    if (!isset($_POST[$key]) || trim($_POST[$key]) === '') {
        redirect_with_error('Missing required field: ' . htmlspecialchars($key));
    }
}

$username = trim($_POST['username']);
$password = $_POST['password'];
$fullName = trim($_POST['fullName']);
$email = trim($_POST['email']);
$mobile = trim($_POST['mobile']);
$address = trim($_POST['address']);
$gradeLevel = $_POST['gradeLevel'] ?? null;
$subject = $_POST['subject'] ?? null;
$nationalId = $_POST['nationalId'] ?? null;

// Input validation
if (strlen($username) < 3 || strlen($username) > 50) {
    redirect_with_error('Username must be 3-50 characters.');
}
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    redirect_with_error('Username can only contain letters, numbers, and underscores.');
}
if (strlen($password) < 6) {
    redirect_with_error('Password must be at least 6 characters.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_error('Please enter a valid email address.');
}
if (strlen($fullName) < 2 || strlen($fullName) > 100) {
    redirect_with_error('Full name must be 2-100 characters.');
}

try {
    // Database connection already available as $db_connection from config.php

    if ($role === 'teacher') {
        // Check username/email in teachers
        $stmt = $db_connection->prepare('SELECT COUNT(*) FROM teachers WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            redirect_with_error('Username or email already exists.');
        }

        $id = 'teach_' . bin2hex(random_bytes(8));
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $insert = $db_connection->prepare('INSERT INTO teachers (id, username, password, fullName, email, mobile, address, specialty, nationalId, createdAt) VALUES (?,?,?,?,?,?,?,?,?,NOW())');
        $insert->execute([$id, $username, $hash, $fullName, $email, $mobile, $address, $subject, $nationalId]);

        header('Location: ../Views/teacher-back-office/login.php?registered=1');
        exit;
    } else {
        // Student path
        $stmt = $db_connection->prepare('SELECT COUNT(*) FROM students WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            redirect_with_error('Username or email already exists.');
        }

        $id = 'stu_' . bin2hex(random_bytes(8));
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $insert = $db_connection->prepare('INSERT INTO students (id, username, password, fullName, email, mobile, address, gradeLevel, createdAt) VALUES (?,?,?,?,?,?,?,?,NOW())');
        $insert->execute([$id, $username, $hash, $fullName, $email, $mobile, $address, $gradeLevel]);

        header('Location: ../Views/front-office/login.php?registered=1');
        exit;
    }
} catch (Exception $e) {
    redirect_with_error('Registration error.');
}
