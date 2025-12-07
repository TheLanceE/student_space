<?php
// Main entry point
require_once __DIR__ . '/controller/quizcontroller.php';

$controller = new QuizController();
$controller->handleRequest();
?>