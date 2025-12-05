<?php
/**
 * Simple Logout Handler
 */

require_once __DIR__ . '/SessionManager.php';

// Use SessionManager to properly destroy session
SessionManager::destroy();

// Redirect to login
header('Location: ../Views/front-office/login.php');
exit;
?>