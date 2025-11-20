<?php
include 'controllers/QuizController.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$query = $_GET;

$quizController = new QuizController();

// Simple routing
if (preg_match('#/quizzes/(\d+)#', $path, $matches)) {
    $id = $matches[1];
    
    switch($method) {
        case 'GET':
            $quizController->getQuiz($id);
            break;
        case 'PUT':
        case 'POST':
            $quizController->updateQuiz($id);
            break;
        case 'DELETE':
            $quizController->deleteQuiz($id);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} else if ($path === '/quizzes' || $path === '/quizzes/') {
    switch($method) {
        case 'GET':
            $quizController->getAllQuizzes();
            break;
        case 'POST':
            $quizController->createQuiz();
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
}
?>