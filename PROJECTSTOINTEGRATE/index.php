<?php
// Temporary: enable display of runtime errors for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug entry point for testing projects.php
session_start();

// Simulate logged-in student for testing
$_SESSION['user'] = [
    'id' => 'stu_debug',
    'username' => 'debug_student',
    'role' => 'student',
    'fullName' => 'Debug Student'
];

// Redirect to projects view
header('Location: Views/projects_student.php');
exit;
