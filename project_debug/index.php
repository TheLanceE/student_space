<?php
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
