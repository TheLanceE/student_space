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

$required = ['username','password','fullName','email','mobile','address','gradeLevel'];
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
$gradeLevel = trim($_POST['gradeLevel']);

try {
    // Database connection already available as $db_connection from config.php

    // Check if username or email exists
    $stmt = $db_connection->prepare('SELECT COUNT(*) FROM students WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetchColumn() > 0) {
        redirect_with_error('Username or email already exists.');
    }

    $id = uniqid('s_');
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $insert = $db_connection->prepare('INSERT INTO students (id, username, password, fullName, email, mobile, address, gradeLevel, createdAt) VALUES (?,?,?,?,?,?,?,?,NOW())');
    $insert->execute([$id, $username, $hash, $fullName, $email, $mobile, $address, $gradeLevel]);

    header('Location: ../Views/front-office/login.php?registered=1');
    exit;
} catch (Exception $e) {
    redirect_with_error('Registration error.');
}
