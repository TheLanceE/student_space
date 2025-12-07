<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../model/QuizModel.php';
require_once __DIR__ . '/../model/Quiz.php';
require_once __DIR__ . '/../model/Question.php';
require_once __DIR__ . '/../model/QuestionOption.php';

session_start();

class QuizController {
    private $quizModel;
    
    public function __construct() {
        $this->quizModel = new QuizModel();
        
        // Initialize session for testing if not set
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['user_id'] = 1;
            $_SESSION['role'] = 'teacher';
            $_SESSION['user_name'] = 'John Smith';
        }
    }
    
    public function handleRequest() {
        $action = $_GET['action'] ?? $_POST['action'] ?? 'index';
        
        switch ($action) {
            case 'create':
                $this->createQuiz();
                break;
            case 'edit':
                $this->editQuiz();
                break;
            case 'getQuizForTaking':
                $this->getQuizForTaking();
                break;
            case 'getMyQuizzes':
                $this->getMyQuizzes();
                break;
            case 'getAllQuizzes':
                $this->getAllQuizzes();
                break;
            case 'delete':
                $this->deleteQuiz();
                break;
            case 'preview':
                $this->previewQuiz();
                break;
            case 'getQuizFormData':
                $this->getQuizFormData();
                break;
            case 'take':
                $this->takeQuiz();
                break;
            case 'submitAttempt':
                $this->submitAttempt();
                break;
            case 'getStats':
                $this->getStudentStats();
                break;
            case 'getAttemptedQuizIds':
                $this->getAttemptedQuizIds();
                break;
            case 'reviewAttempt':
                $this->reviewAttempt();
                break;
            default:
                $this->index();
        }
    }

    // Returns quiz markup for students to take the quiz (no answers revealed)
    private function getQuizForTaking() {
        $quizId = $_GET['id'] ?? null;
        if (!$quizId) {
            http_response_code(400);
            echo "<div style='padding: 20px; color: red;'>Quiz ID required</div>";
            return;
        }

        try {
            $data = $this->quizModel->getQuizById($quizId);
            if (!$data) {
                http_response_code(404);
                echo "<div style='padding: 20px; color: red;'>Quiz not found</div>";
                return;
            }

            $quiz = $data['quiz'];
            $questions = $data['questions'];

            // Build simple HTML structure with IDs for posting back
            echo "<div class='quiz-take' data-quiz-id='" . htmlspecialchars($quiz->getId()) . "'>";
            echo "<h3>" . htmlspecialchars($quiz->getTitle()) . "</h3>";
            foreach ($questions as $idx => $q) {
                $question = $q['question'];
                echo "<div class='quiz-q' data-question-id='" . intval($question->getId()) . "' style='background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:16px;margin-bottom:12px;'>";
                echo "<div style='font-weight:bold;color:#4361ee;margin-bottom:8px;'>Question " . ($idx+1) . "</div>";
                echo "<div class='q-text' style='margin-bottom:10px;'>" . htmlspecialchars($question->getQuestionText()) . "</div>";
                echo "<div class='q-options'>";
                foreach ($q['options'] as $opt) {
                    $oid = intval($opt->getId());
                    $label = htmlspecialchars($opt->getOptionLabel());
                    $text = htmlspecialchars($opt->getOptionText());
                    $name = 'question_' . intval($question->getId());
                    echo "<label style='display:block;padding:8px;border:1px solid #ddd;border-radius:6px;margin-bottom:8px;cursor:pointer;'>";
                    echo "<input type='radio' name='" . $name . "' value='" . $oid . "' style='margin-right:8px;'>";
                    echo "<strong>" . $label . ".</strong> " . $text;
                    echo "</label>";
                }
                echo "</div>"; // options
                echo "</div>"; // question
            }
            echo "</div>";
        } catch (Exception $e) {
            http_response_code(500);
            echo "<div style='padding: 20px; color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
private function createQuiz() {
    error_log("Create quiz method called");
    error_log("POST data: " . print_r($_POST, true));
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("Method not allowed");
        header("Location: ../view/teacher_quizzes.php?error=Method not allowed");
        exit();
    }
        
        try {
            // Create quiz object
            $quiz = new Quiz(
                null,
                $_POST['title'],
                $_POST['category'],
                $_POST['passing_grade'],
                $_POST['description'] ?? '',
                $_POST['status'] ?? 'draft',
                $_POST['gradeLevel'],
                $_POST['time_limit'] ?? 0
            );
            
            // Process questions
            $questions = [];
            if (isset($_POST['questions']) && is_array($_POST['questions'])) {
                $questionOrder = 0;
                foreach ($_POST['questions'] as $questionData) {
                    if (empty(trim($questionData['text']))) continue;
                    
                    $question = new Question(
                        null,
                        null,
                        $questionData['text'],
                        $questionOrder
                    );
                    
                    $options = [];
                    if (isset($questionData['options']) && is_array($questionData['options'])) {
                        $optionIndex = 0;
                        foreach ($questionData['options'] as $optionData) {
                            if (empty(trim($optionData['text']))) continue;
                            
                            $optionLabel = chr(65 + $optionIndex);
                            $isCorrect = (isset($optionData['is_correct']) && $optionData['is_correct'] == '1');
                            
                            $options[] = new QuestionOption(
                                null,
                                null,
                                $optionLabel,
                                $optionData['text'],
                                $isCorrect
                            );
                            $optionIndex++;
                        }
                    }
                    
                    $questions[] = [
                        'question' => $question,
                        'options' => $options
                    ];
                    $questionOrder++;
                }
            }
            
            $quizId = $this->quizModel->createQuiz($quiz, $questions);
            if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
                header('Content-Type: text/plain');
                echo 'quiz_id=' . $quizId;
                exit();
            } else {
                header("Location: ../view/teacher_quizzes.php?success=1&quiz_id=" . $quizId);
                exit();
            }
            
        } catch (Exception $e) {
            header("Location: ../view/teacher_quizzes.php?error=" . urlencode($e->getMessage()));
            exit();
        }
    }
    
    private function getMyQuizzes() {
        if ($_SESSION['role'] !== 'teacher') {
            http_response_code(403);
            echo "Access denied";
            return;
        }
        
        $quizzes = $this->quizModel->getQuizzesByTeacher($_SESSION['user_id']);
        
        if (empty($quizzes)) {
            echo '<tr><td colspan="6" class="text-center">No quizzes found</td></tr>';
            return;
        }
        
        foreach ($quizzes as $quiz) {
            $statusBadge = $quiz->getStatus() === 'active' ? 
                '<span class="badge bg-success">Active</span>' : 
                '<span class="badge bg-warning">Draft</span>';
            
            // Get question count for this quiz
            $quizData = $this->quizModel->getQuizById($quiz->getId());
            $questionCount = $quizData ? count($quizData['questions']) : 0;
            
            echo "
            <tr>
                <td>{$quiz->getTitle()}</td>
                <td><span class=\"badge bg-primary\">{$quiz->getCategory()}</span></td>
                <td>{$questionCount}</td>
                <td>{$statusBadge}</td>
                <td>{$quiz->getCreatedAt()}</td>
                <td>
                    <button class=\"btn btn-sm btn-outline-secondary\" onclick=\"editQuiz({$quiz->getId()})\">Edit</button>
                    <button class=\"btn btn-sm btn-outline-primary\" onclick=\"previewQuiz({$quiz->getId()})\">Preview</button>
                    <button class=\"btn btn-sm btn-outline-danger\" onclick=\"deleteQuiz({$quiz->getId()})\">Delete</button>
                </td>
            </tr>";
        }
    }
    
    private function getAllQuizzes() {
        $quizzesData = $this->quizModel->getQuizzesForBrowse();
        
        if (empty($quizzesData)) {
            echo '<tr><td colspan="7" class="text-center">No quizzes found</td></tr>';
            return;
        }
        
        foreach ($quizzesData as $data) {
            $quiz = $data['quiz'];
            $statusBadge = $quiz->getStatus() === 'active' ? 
                '<span class="badge bg-success">Active</span>' : 
                '<span class="badge bg-warning">Draft</span>';
            
            echo "
            <tr>
                <td>{$quiz->getTitle()}</td>
                <td>{$data['teacher_name']}</td>
                <td><span class=\"badge bg-primary\">{$quiz->getCategory()}</span></td>
                <td>{$quiz->getGrade()}</td>
                <td>{$statusBadge}</td>
                <td>{$quiz->getCreatedAt()}</td>
                <td>
                    <button class=\"btn btn-sm btn-outline-primary\" onclick=\"previewQuiz({$quiz->getId()})\">Preview</button>
                    <button class=\"btn btn-sm btn-outline-danger\" onclick=\"deleteQuiz({$quiz->getId()})\">Delete</button>
                </td>
            </tr>";
        }
    }
    
    private function deleteQuiz() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method not allowed";
            return;
        }
        
        $quizId = $_POST['quiz_id'] ?? null;
        if (!$quizId) {
            http_response_code(400);
            echo "Quiz ID required";
            return;
        }
        
        try {
            $success = $this->quizModel->deleteQuiz($quizId, $_SESSION['user_id']);
            if ($success) {
                echo "Quiz deleted successfully";
            } else {
                http_response_code(404);
                echo "Quiz not found or access denied";
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error: " . $e->getMessage();
        }
    }
    
    private function previewQuiz() {
        try {
            error_log("Preview quiz called");
            $quizId = $_GET['id'] ?? null;
            error_log("Quiz ID: " . $quizId);
            
            if (!$quizId) {
                http_response_code(200);
                echo "<div style='padding: 20px; color: red;'><strong>Error:</strong> Quiz ID required</div>";
                return;
            }
            
            $quizData = $this->quizModel->getQuizById($quizId);
            error_log("Quiz data retrieved: " . ($quizData ? "yes" : "no"));
            
            if (!$quizData) {
                error_log("getQuizById returned null for ID: " . $quizId);
                http_response_code(200);
                echo "<div style='padding: 20px; color: red;'><strong>Error:</strong> Quiz not found in database (ID: {$quizId}). The quiz may have been deleted or the database connection failed.</div>";
                return;
            }
            
            if (!isset($quizData['quiz'])) {
                error_log("Quiz data structure invalid - missing 'quiz' key");
                http_response_code(200);
                echo "<div style='padding: 20px; color: red;'><strong>Error:</strong> Invalid quiz data structure</div>";
                return;
            }
            
            $quiz = $quizData['quiz'];
            $questions = $quizData['questions'];
        
        echo "<div class='quiz-preview' style='padding: 20px;'>";
        echo "<h3 style='margin-bottom: 15px; color: #333;'>{$quiz->getTitle()}</h3>";
        if ($quiz->getDescription()) {
            echo "<p style='color: #666; margin-bottom: 20px;'>{$quiz->getDescription()}</p>";
        }
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 25px;'>";
        echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 10px;'>";
        echo "<div><strong>Category:</strong> {$quiz->getCategory()}</div>";
        echo "<div><strong>Grade Level:</strong> {$quiz->getGrade()}</div>";
        echo "<div><strong>Teacher:</strong> {$quizData['teacher_name']}</div>";
        echo "<div><strong>Passing Grade:</strong> {$quiz->getPassingGrade()}%</div>";
        if ($quiz->getTimeLimit() > 0) {
            echo "<div><strong>Time Limit:</strong> {$quiz->getTimeLimit()} minutes</div>";
        }
        echo "<div><strong>Total Questions:</strong> " . count($questions) . "</div>";
        echo "</div></div>";
        
        if (empty($questions)) {
            echo "<div style='background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin-top: 20px; text-align: center;'>";
            echo "<i class='fas fa-exclamation-triangle' style='color: #856404; font-size: 48px; margin-bottom: 15px;'></i>";
            echo "<h4 style='color: #856404; margin-bottom: 10px;'>No Questions Added Yet</h4>";
            echo "<p style='color: #856404;'>This quiz doesn't have any questions yet. Please edit the quiz to add questions.</p>";
            echo "</div>";
        }
        
        foreach ($questions as $index => $q) {
            $question = $q['question'];
            echo "<div style='background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 20px;'>";
            echo "<h5 style='color: #4361ee; margin-bottom: 15px; font-size: 18px;'>Question " . ($index + 1) . "</h5>";
            echo "<p style='font-size: 16px; margin-bottom: 15px; color: #333;'>{$question->getQuestionText()}</p>";
            echo "<div style='margin-left: 10px;'>";
            
            foreach ($q['options'] as $option) {
                if ($option->getIsCorrect()) {
                    echo "<div style='padding: 12px; margin-bottom: 8px; border: 2px solid #2ecc71; background: #e8f5e8; border-radius: 6px;'>";
                    echo "<strong style='color: #2ecc71;'>{$option->getOptionLabel()}.</strong> ";
                    echo "<span style='color: #333;'>{$option->getOptionText()}</span>";
                    echo " <span style='background: #2ecc71; color: white; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; margin-left: 10px;'>Correct Answer</span>";
                    echo "</div>";
                } else {
                    echo "<div style='padding: 12px; margin-bottom: 8px; border: 1px solid #ddd; background: #fff; border-radius: 6px;'>";
                    echo "<strong style='color: #666;'>{$option->getOptionLabel()}.</strong> ";
                    echo "<span style='color: #333;'>{$option->getOptionText()}</span>";
                    echo "</div>";
                }
            }
            
            echo "</div></div>";
        }
        echo "</div>";
        
        } catch (Exception $e) {
            error_log("Preview error: " . $e->getMessage());
            http_response_code(200);
            echo "<div style='padding: 20px; color: red;'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }

    private function editQuiz() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../view/teacher_quizzes.php?error=Method not allowed");
            exit();
        }
        $quizId = $_POST['quiz_id'] ?? null;
        if (!$quizId) {
            header("Location: ../view/teacher_quizzes.php?error=Quiz ID required");
            exit();
        }
        try {
            $quiz = new Quiz(
                $quizId,
                $_POST['title'],
                $_POST['category'],
                $_POST['passing_grade'],
                $_POST['description'] ?? '',
                $_POST['status'] ?? 'draft',
                $_POST['gradeLevel'],
                $_POST['time_limit'] ?? 0
            );
            $questions = [];
            if (isset($_POST['questions']) && is_array($_POST['questions'])) {
                $order = 0;
                foreach ($_POST['questions'] as $q) {
                    if (empty(trim($q['text']))) continue;
                    $question = new Question(null, null, $q['text'], $order);
                    $opts = [];
                    if (isset($q['options']) && is_array($q['options'])) {
                        $idx = 0;
                        foreach ($q['options'] as $opt) {
                            if (empty(trim($opt['text']))) continue;
                            $label = chr(65 + $idx);
                            $isCorrect = (isset($opt['is_correct']) && $opt['is_correct'] == '1');
                            $opts[] = new QuestionOption(null, null, $label, $opt['text'], $isCorrect);
                            $idx++;
                        }
                    }
                    $questions[] = ['question' => $question, 'options' => $opts];
                    $order++;
                }
            }
            $this->quizModel->updateQuiz($quizId, $quiz, $questions);
            if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
                header('Content-Type: text/plain');
                echo 'quiz_id=' . $quizId;
                exit();
            } else {
                header("Location: ../view/teacher_quizzes.php?success=1&updated=1&quiz_id=" . $quizId);
                exit();
            }
        } catch (Exception $e) {
            header("Location: ../view/teacher_quizzes.php?error=" . urlencode($e->getMessage()));
            exit();
        }
    }

    private function getQuizFormData() {
        $quizId = $_GET['id'] ?? null;
        if (!$quizId) {
            http_response_code(400);
            echo "error=Quiz+ID+required";
            return;
        }
        $data = $this->quizModel->getQuizById($quizId);
        if (!$data) {
            http_response_code(404);
            echo "error=Quiz+not+found";
            return;
        }
        $quiz = $data['quiz'];
        $qs = $data['questions'];
        $params = [];
        $params[] = 'quiz_id=' . urlencode($quiz->getId());
        $params[] = 'title=' . urlencode($quiz->getTitle());
        $params[] = 'category=' . urlencode($quiz->getCategory());
        $params[] = 'gradeLevel=' . urlencode($quiz->getGrade());
        $params[] = 'description=' . urlencode($quiz->getDescription());
        $params[] = 'status=' . urlencode($quiz->getStatus());
        $params[] = 'passing_grade=' . urlencode($quiz->getPassingGrade());
        $params[] = 'time_limit=' . urlencode($quiz->getTimeLimit());
        foreach ($qs as $i => $q) {
            $params[] = 'questions['.$i.'][text]=' . urlencode($q['question']->getQuestionText());
            foreach ($q['options'] as $j => $opt) {
                $params[] = 'questions['.$i.'][options]['.$j.'][text]=' . urlencode($opt->getOptionText());
                $params[] = 'questions['.$i.'][options]['.$j.'][is_correct]=' . ($opt->getIsCorrect() ? '1' : '0');
            }
        }
        header('Content-Type: text/plain');
        echo implode('&', $params);
    }
    
    private function takeQuiz() {
        // This would render the quiz taking interface
        // Implementation depends on your view structure
    }
    
    private function submitAttempt() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method not allowed";
            return;
        }
        
        try {
            $quizId = $_POST['quiz_id'];
            $studentId = $_SESSION['user_id'];
            $answers = [];
            
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'question_') === 0) {
                    $questionId = substr($key, 9);
                    $answers[$questionId] = $value;
                }
            }
            
            $result = $this->quizModel->saveQuizAttempt($quizId, $studentId, $answers);
            
            // Display results
            echo "<div class='card'>";
            echo "<div class='card-body text-center'>";
            echo "<h3>Quiz Completed!</h3>";
            echo "<div class='display-4 text-" . ($result['score'] >= 70 ? 'success' : 'danger') . "'>";
            echo "{$result['score']}%";
            echo "</div>";
            echo "<p class='text-muted'>You got {$result['correct_answers']} out of {$result['total_questions']} correct</p>";
            
            if ($result['score'] >= 70) {
                echo "<div class='alert alert-success'>Congratulations! You passed the quiz.</div>";
            } else {
                echo "<div class='alert alert-warning'>Keep practicing! You need 70% to pass.</div>";
            }
            // Actions: Review Quiz and Back to Quizzes
            echo "<div class='mt-3'>";
            echo "<a href='#' id='reviewAttemptBtn' data-attempt='" . intval($result['attempt_id']) . "' class='btn btn-outline-secondary me-2'>Review Quiz</a>";
            echo "<a href='student_quizzes.php' class='btn btn-primary'>Back to Quizzes</a>";
            echo "</div>";
            echo "</div></div>";
            
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error submitting quiz: " . $e->getMessage();
        }
    }

    private function reviewAttempt() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo "Access denied";
            return;
        }

        $attemptId = $_GET['id'] ?? null;
        if (!$attemptId) {
            http_response_code(400);
            echo "<div style='padding: 20px; color: red;'>Attempt ID required</div>";
            return;
        }

        $data = $this->quizModel->getAttemptWithDetails($attemptId, $_SESSION['user_id']);
        if (!$data) {
            http_response_code(404);
            echo "<div style='padding: 20px; color: red;'>Attempt not found</div>";
            return;
        }

        $quiz = $data['quiz'];
        $questions = $data['questions'];

        echo "<div class='card'><div class='card-body'>";
        echo "<h3 style='margin-bottom: 15px;'>Review: " . htmlspecialchars($quiz->getTitle()) . "</h3>";
        foreach ($questions as $idx => $q) {
            $question = $q['question'];
            $selectedId = $q['selected_option_id'];

            echo "<div style='background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:16px;margin-bottom:12px;'>";
            echo "<div style='font-weight:bold;color:#4361ee;margin-bottom:8px;'>Question " . ($idx+1) . "</div>";
            echo "<div style='margin-bottom:10px;'>" . htmlspecialchars($question->getQuestionText()) . "</div>";

            foreach ($q['options'] as $opt) {
                $isCorrect = $opt->getIsCorrect();
                $isSelected = ($selectedId && intval($opt->getId()) === intval($selectedId));
                $border = $isCorrect ? '2px solid #2ecc71' : ($isSelected ? '2px solid #e63946' : '1px solid #ddd');
                $bg = $isCorrect ? '#e8f5e8' : ($isSelected ? '#fdeaea' : '#fff');
                $label = htmlspecialchars($opt->getOptionLabel());
                $text = htmlspecialchars($opt->getOptionText());
                echo "<div style='padding:12px;border:{$border};background:{$bg};border-radius:6px;margin-bottom:8px;'>";
                echo "<strong>" . $label . ".</strong> " . $text;
                if ($isCorrect) {
                    echo " <span style='background:#2ecc71;color:#fff;padding:3px 8px;border-radius:4px;font-size:12px;margin-left:8px;'>Correct</span>";
                }
                if ($isSelected && !$isCorrect) {
                    echo " <span style='background:#e63946;color:#fff;padding:3px 8px;border-radius:4px;font-size:12px;margin-left:8px;'>Your Answer</span>";
                }
                if ($isSelected && $isCorrect) {
                    echo " <span style='background:#2ecc71;color:#fff;padding:3px 8px;border-radius:4px;font-size:12px;margin-left:8px;'>Your Answer</span>";
                }
                echo "</div>";
            }

            // Summary line
            $wasCorrect = false;
            foreach ($q['options'] as $opt) {
                if ($opt->getIsCorrect() && intval($opt->getId()) === intval($selectedId)) { $wasCorrect = true; break; }
            }
            echo "<div style='margin-top:8px;font-weight:bold;color:" . ($wasCorrect ? '#2ecc71' : '#e63946') . "'>" . ($wasCorrect ? 'Correct' : 'Incorrect') . "</div>";

            echo "</div>"; // question card
        }

        echo "<div class='text-center mt-3'><a href='student_quizzes.php' class='btn btn-primary'>Back to Quizzes</a></div>";
        echo "</div></div>";
    }
    
    private function getStudentStats() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo "Access denied";
            return;
        }

        $stats = $this->quizModel->getStudentStats($_SESSION['user_id']);
        header('Content-Type: text/html');
        
        echo "<div class='row g-3 mb-4'>";
        echo "<div class='col-12 col-sm-6 col-lg-3'>";
        echo "<div class='stat'><div class='text-muted small'><i class='bi bi-trophy-fill me-1'></i>Quizzes Completed</div>";
        echo "<div class='h4 mb-0'>" . ($stats['completed_quizzes'] ?? 0) . "</div></div></div>";
        
        echo "<div class='col-12 col-sm-6 col-lg-3'>";
        echo "<div class='stat'><div class='text-muted small'><i class='bi bi-percent me-1'></i>Average Score</div>";
        echo "<div class='h4 mb-0'>" . round($stats['average_score'] ?? 0, 1) . "%</div></div></div>";
        
        echo "<div class='col-12 col-sm-6 col-lg-3'>";
        echo "<div class='stat'><div class='text-muted small'><i class='bi bi-star-fill me-1'></i>Best Score</div>";
        echo "<div class='h4 mb-0'>" . round($stats['best_score'] ?? 0, 1) . "%</div></div></div>";
        
        echo "<div class='col-12 col-sm-6 col-lg-3'>";
        echo "<div class='stat'><div class='text-muted small'><i class='bi bi-clock me-1'></i>In Progress</div>";
        echo "<div class='h4 mb-0'>0</div></div></div>";
        echo "</div>";
    }

    private function getAttemptedQuizIds() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo "Access denied";
            return;
        }

        try {
            $ids = $this->quizModel->getAttemptedQuizIds($_SESSION['user_id']);
            header('Content-Type: text/html');
            echo "<ul class='attempted-quiz-ids' style='display:none;'>";
            foreach ($ids as $id) {
                $safe = intval($id);
                echo "<li data-quiz-id='{$safe}'></li>";
            }
            echo "</ul>";
        } catch (Exception $e) {
            http_response_code(500);
            echo "<div>Error loading attempts</div>";
        }
    }
    
    private function index() {
        // Default action - redirect based on role
        if ($_SESSION['role'] === 'teacher') {
            header("Location: ../view/teacher_quizzes.php");
        } else {
            header("Location: ../view/student_quizzes.php");
        }
        exit();
    }
}

// Handle the request
$controller = new QuizController();
$controller->handleRequest();
?>
