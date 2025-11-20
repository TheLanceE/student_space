<?php
// Quiz Controller - File-based storage (no DB required, non-HTML5 compatible)
// Stores quizzes as JSON files on the server

class QuizController {
    private $quizzesDir;

    public function __construct() {
        // Create a quizzes storage directory
        $this->quizzesDir = __DIR__ . '/../quizzes_data';
        if (!is_dir($this->quizzesDir)) {
            mkdir($this->quizzesDir, 0755, true);
        }
    }

    // CREATE - Save new quiz (file-based)
    public function createQuiz() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        try {
            $quizData = json_decode(file_get_contents('php://input'), true);
            
            if (!$quizData['title']) {
                throw new Exception('Quiz title is required');
            }

            // Generate unique quiz ID
            $quizId = 'quiz_' . time() . '_' . uniqid();
            
            // Add metadata
            $quizData['id'] = $quizId;
            $quizData['created_at'] = date('Y-m-d H:i:s');
            $quizData['created_by'] = 'you';
            
            // Save to file
            $filePath = $this->quizzesDir . '/' . $quizId . '.json';
            file_put_contents($filePath, json_encode($quizData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            echo json_encode([
                'success' => true, 
                'quiz_id' => $quizId, 
                'message' => 'Quiz created successfully!'
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to create quiz: ' . $e->getMessage()
            ]);
        }
    }

    // READ - Get all quizzes (file-based)
    public function getAllQuizzes() {
        header('Content-Type: application/json');
        
        try {
            $quizzes = [];
            $files = glob($this->quizzesDir . '/*.json');
            
            if ($files) {
                foreach ($files as $file) {
                    $content = file_get_contents($file);
                    $quiz = json_decode($content, true);
                    if ($quiz) {
                        $quizzes[] = $quiz;
                    }
                }
            }

            // Sort by created_at descending
            usort($quizzes, function($a, $b) {
                return strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0');
            });

            echo json_encode([
                'success' => true, 
                'quizzes' => $quizzes
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to fetch quizzes: ' . $e->getMessage()
            ]);
        }
    }

    // READ - Get single quiz (file-based)
    public function getQuiz($id) {
        header('Content-Type: application/json');
        
        try {
            $filePath = $this->quizzesDir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $id) . '.json';
            
            if (!file_exists($filePath)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Quiz not found']);
                return;
            }

            $content = file_get_contents($filePath);
            $quiz = json_decode($content, true);

            echo json_encode([
                'success' => true,
                'quiz' => $quiz
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to fetch quiz: ' . $e->getMessage()
            ]);
        }
    }

    // UPDATE - Update quiz (file-based)
    public function updateQuiz($id) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        try {
            $quizData = json_decode(file_get_contents('php://input'), true);
            $filePath = $this->quizzesDir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $id) . '.json';
            
            if (!file_exists($filePath)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Quiz not found']);
                return;
            }

            $quizData['id'] = $id;
            $quizData['updated_at'] = date('Y-m-d H:i:s');
            
            file_put_contents($filePath, json_encode($quizData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            echo json_encode([
                'success' => true, 
                'message' => 'Quiz updated successfully!'
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to update quiz: ' . $e->getMessage()
            ]);
        }
    }

    // DELETE - Delete quiz (file-based)
    public function deleteQuiz($id) {
        header('Content-Type: application/json');
        
        try {
            $filePath = $this->quizzesDir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $id) . '.json';
            
            if (!file_exists($filePath)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Quiz not found']);
                return;
            }

            unlink($filePath);

            echo json_encode([
                'success' => true, 
                'message' => 'Quiz deleted successfully!'
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to delete quiz: ' . $e->getMessage()
            ]);
        }
    }
}

// Router - handle action parameter
header('Content-Type: application/json');
$action = $_GET['action'] ?? 'getAllQuizzes';
$controller = new QuizController();

// Get ID from query param (supports alphanumeric IDs created by this file-based store)
$id = $_GET['id'] ?? null;
if ($id) {
    $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $id);
} else {
    // Fallback: try to extract numeric id from path (legacy behavior)
    $id = null;
    if (preg_match('/\/quiz\/(\d+)/', $_SERVER['REQUEST_URI'], $matches)) {
        $id = $matches[1];
    }
}

switch ($action) {
    case 'createQuiz':
        $controller->createQuiz();
        break;
    case 'getAllQuizzes':
        $controller->getAllQuizzes();
        break;
    case 'getQuiz':
        if ($id) {
            $controller->getQuiz($id);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Quiz ID required']);
        }
        break;
    case 'updateQuiz':
        if ($id) {
            $controller->updateQuiz($id);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Quiz ID required']);
        }
        break;
    case 'deleteQuiz':
        if ($id) {
            $controller->deleteQuiz($id);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Quiz ID required']);
        }
        break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Action not found']);
}
?>