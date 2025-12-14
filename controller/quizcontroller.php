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
            case 'generateQuestions':
                $this->generateQuestions();
                break;
            case 'saveGeneratedQuiz':
                $this->saveGeneratedQuiz();
                break;
            case 'aiStatus':
                $this->aiStatus();
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
    
    private function generateQuestions() {
        // JSON used ONLY for AI API communication (DeepSeek/Gemini)
        header('Content-Type: application/json');
        @ini_set('display_errors', 0);
        @ini_set('html_errors', 0);
        
        try {
            if (!isset($_SESSION['last_ai_request'])) { $_SESSION['last_ai_request'] = 0; }
            if (time() - intval($_SESSION['last_ai_request']) < 5) {
                echo json_encode(['success' => false, 'error' => 'Please wait 5 seconds between requests']);
                return;
            }
            $_SESSION['last_ai_request'] = time();

            $title = filter_var($_POST['title'] ?? '', FILTER_SANITIZE_STRING);
            $category = filter_var($_POST['category'] ?? '', FILTER_SANITIZE_STRING);
            $grade = filter_var($_POST['grade'] ?? '', FILTER_SANITIZE_STRING);
            $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_STRING);
            $questionCount = filter_var($_POST['questionCount'] ?? 5, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 20]
            ]);
            if ($questionCount === false) { $questionCount = 5; }
            
            if (empty($title) || empty($category) || empty($grade)) {
                echo json_encode(['success' => false, 'error' => 'Title, category, and grade are required.']);
                return;
            }
            
            // Load AI provider + API key from config
            $configPath = __DIR__ . '/../ai_config.php';
            if (!file_exists($configPath)) {
                echo json_encode(['success' => false, 'error' => 'AI configuration file not found']);
                return;
            }
            
            require_once $configPath;
            if (!defined('AI_PROVIDER') || !defined('AI_API_KEY') || empty(AI_API_KEY)) {
                echo json_encode(['success' => false, 'error' => 'AI provider or API key not configured.']);
                return;
            }
            
            // Build prompt for AI
            $categoryName = ucfirst(str_replace('-', ' ', $category));
            
            // Parse description for specific topics/constraints
            $descLower = strtolower($description);
            $topicConstraints = '';
            
            // Math-specific constraints
            $mathTopics = [];
            if (preg_match('/addition/i', $description)) $mathTopics[] = 'addition';
            if (preg_match('/subtraction/i', $description)) $mathTopics[] = 'subtraction';
            if (preg_match('/multiplication/i', $description)) $mathTopics[] = 'multiplication';
            if (preg_match('/division/i', $description)) $mathTopics[] = 'division';
            if (preg_match('/fraction|fractions/i', $description)) $mathTopics[] = 'fractions';
            if (preg_match('/decimal|decimals/i', $description)) $mathTopics[] = 'decimals';
            if (preg_match('/percent|percentage/i', $description)) $mathTopics[] = 'percentages';
            if (preg_match('/algebra|variable|equation|solve/i', $description)) $mathTopics[] = 'algebra';
            if (preg_match('/geometry|shape|triangle|square|circle|polygon/i', $description)) $mathTopics[] = 'geometry';
            if (preg_match('/measurement|measure|length|distance|weight|mass|volume/i', $description)) $mathTopics[] = 'measurement';
            
            // Science-specific constraints
            $scienceTopics = [];
            if (preg_match('/photosynthesis/i', $description)) $scienceTopics[] = 'photosynthesis';
            if (preg_match('/respiration/i', $description)) $scienceTopics[] = 'respiration';
            if (preg_match('/plant|root|stem|leaf|flower/i', $description)) $scienceTopics[] = 'plant biology';
            if (preg_match('/animal|organism|creature|insect|mammal|bird/i', $description)) $scienceTopics[] = 'animal biology';
            if (preg_match('/cell|nucleus|mitochondria|dna|protein/i', $description)) $scienceTopics[] = 'cell biology';
            if (preg_match('/atom|molecule|element|compound|chemical/i', $description)) $scienceTopics[] = 'chemistry';
            if (preg_match('/force|motion|speed|velocity|acceleration|gravity/i', $description)) $scienceTopics[] = 'physics';
            if (preg_match('/earth|planet|solar|moon|star|galaxy|space/i', $description)) $scienceTopics[] = 'astronomy';
            if (preg_match('/weather|climate|temperature|precipitation|wind/i', $description)) $scienceTopics[] = 'weather and climate';
            
            // English-specific constraints
            $englishTopics = [];
            if (preg_match('/noun|verb|adjective|adverb|pronoun|preposition|conjunction|interjection/i', $description)) $englishTopics[] = 'parts of speech';
            if (preg_match('/sentence|grammar|tense|subject|predicate|clause/i', $description)) $englishTopics[] = 'grammar';
            if (preg_match('/vocabulary|vocab|word|meaning|synonym|antonym/i', $description)) $englishTopics[] = 'vocabulary';
            if (preg_match('/reading|comprehension|passage|text/i', $description)) $englishTopics[] = 'reading comprehension';
            if (preg_match('/writing|essay|paragraph|punctuation|capitalize/i', $description)) $englishTopics[] = 'writing';
            if (preg_match('/literature|novel|poem|story|character|plot/i', $description)) $englishTopics[] = 'literature';
            if (preg_match('/idiom|phrase|expression/i', $description)) $englishTopics[] = 'idioms and expressions';
            
            // Build topic constraint
            if (!empty($mathTopics)) {
                $topicConstraints = 'Generate questions ONLY about: ' . implode(', ', array_unique($mathTopics));
            } elseif (!empty($scienceTopics)) {
                $topicConstraints = 'Generate questions ONLY about: ' . implode(', ', array_unique($scienceTopics));
            } elseif (!empty($englishTopics)) {
                $topicConstraints = 'Generate questions ONLY about: ' . implode(', ', array_unique($englishTopics));
            }
            
            $prompt = "Generate exactly {$questionCount} quiz questions in JSON format.\n\n";
            $prompt .= "Quiz Details:\n";
            $prompt .= "Title: {$title}\n";
            $prompt .= "Category: {$categoryName}\n";
            $prompt .= "Grade Level: Grade {$grade}\n";
            if (!empty($description)) {
                $prompt .= "Description: {$description}\n";
            }
            $prompt .= "\nRequirements:\n";
            $prompt .= "- Create {$questionCount} questions appropriate for grade {$grade}\n";
            $prompt .= "- Use this type distribution: 60% multiple_choice, 20% true_false, 20% short_answer\n";
            $prompt .= "- For true_false and short_answer, STILL provide exactly 4 options (A, B, C, D) by including plausible distractors; mark the correct option\n";
            $prompt .= "- Include a 'type' field for each question with one of: multiple_choice | true_false | short_answer\n";
            $prompt .= "- Each question must have exactly 4 options (A, B, C, D)\n";
            $prompt .= "- Include the correct answer index (0-3)\n\n";
            $prompt .= "Strict constraints:\n";
            $prompt .= "- Focus ONLY on the specified category: {$categoryName}. Do NOT include topics from other domains.\n";
            if (!empty($topicConstraints)) {
                $prompt .= "- " . $topicConstraints . ". Do NOT generate questions about other topics.\n";
            }
            $prompt .= "- Make questions domain-specific and age-appropriate for Grade {$grade}.\n\n";
            $prompt .= "Return ONLY a JSON array with this exact structure:\n";
            $prompt .= '[{"text":"question text","type":"multiple_choice","options":["A","B","C","D"],"correctAnswer":0}]';
            $prompt .= "\n\nNo markdown, no code blocks, just the JSON array.";
            
            $apiKey = AI_API_KEY;
            $provider = AI_PROVIDER;
            
            if ($provider === 'gemini') {
                // Gemini generateContent
                $url = AI_BASE_URL . '/v1beta/models/gemini-pro:generateContent?key=' . $apiKey;
                $requestData = array(
                    'contents' => array(
                        array(
                            'parts' => array(
                                array('text' => $prompt)
                            )
                        )
                    ),
                    'generationConfig' => array(
                        'temperature' => 0.7,
                        'maxOutputTokens' => 2048
                    )
                );
            } else {
                // DeepSeek (OpenAI-compatible chat completions)
                $url = AI_BASE_URL . '/v1/chat/completions';
                $requestData = array(
                    'model' => 'deepseek-chat',
                    'messages' => array(
                        array('role' => 'system', 'content' => 'You are an expert educator. Generate quiz questions appropriate for the grade level.'),
                        array('role' => 'user', 'content' => $prompt)
                    ),
                    'temperature' => 0.7,
                    'max_tokens' => 2000
                );
            }

            $ch = curl_init();
            $headers = array('Content-Type: application/json');
            if ($provider === 'deepseek') {
                $headers[] = 'Authorization: Bearer ' . $apiKey;
            }
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => json_encode($requestData)
            ));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                $errorData = json_decode($response, true);
                $errorMsg = isset($errorData['error']['message']) ? $errorData['error']['message'] : 'API request failed';
                $errLower = strtolower($errorMsg);
                $isBalance = ($httpCode === 402) || (strpos($errLower, 'insufficient') !== false && strpos($errLower, 'balance') !== false);
                if ($isBalance) {
                    $fallback = $this->buildFallbackQuestions($title, $categoryName, $grade, $description, $questionCount);
                    echo json_encode(['success' => true, 'questions' => $fallback, 'fallback' => true, 'note' => 'AI balance insufficient. Used offline generator.']);
                    return;
                }
                echo json_encode(['success' => false, 'error' => $errorMsg]);
                return;
            }
            
            $result = json_decode($response, true);
            
            $content = '';
            if ($provider === 'gemini') {
                if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    echo json_encode(['success' => false, 'error' => 'Invalid API response']);
                    return;
                }
                $content = $result['candidates'][0]['content']['parts'][0]['text'];
            } else {
                if (!isset($result['choices'][0]['message']['content'])) {
                    $err = isset($result['error']['message']) ? $result['error']['message'] : 'Invalid API response';
                    echo json_encode(['success' => false, 'error' => $err]);
                    return;
                }
                $content = $result['choices'][0]['message']['content'];
            }
            
            // Clean up response - remove markdown code blocks if present
            $content = trim($content);
            $content = preg_replace('/^```json\s*/m', '', $content);
            $content = preg_replace('/^```\s*/m', '', $content);
            $content = trim($content);
            
            // Extract JSON array
            if (strpos($content, '[') !== false) {
                $jsonStart = strpos($content, '[');
                $jsonEnd = strrpos($content, ']');
                if ($jsonStart !== false && $jsonEnd !== false) {
                    $content = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
                }
            }
            
            $questions = json_decode($content, true);
            
            if (!is_array($questions)) {
                echo json_encode(['success' => false, 'error' => 'Failed to parse AI response']);
                return;
            }
            
            // Validate and clean questions
            $validQuestions = array();
            foreach ($questions as $q) {
                if (isset($q['text']) && isset($q['options']) && is_array($q['options']) && count($q['options']) === 4 && isset($q['correctAnswer'])) {
                    if (!isset($q['type'])) { $q['type'] = 'multiple_choice'; }
                    $validQuestions[] = $q;
                }
            }
            
            if (empty($validQuestions)) {
                $fallback = $this->buildFallbackQuestions($title, $categoryName, $grade, $description, $questionCount);
                echo json_encode(['success' => true, 'questions' => $fallback, 'fallback' => true, 'note' => 'AI returned no valid items. Used offline generator.']);
                return;
            }
            
            echo json_encode(['success' => true, 'questions' => $validQuestions]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    private function buildFallbackQuestions($title, $categoryName, $grade, $description, $count) {
        $questions = [];
        $topic = trim(preg_replace('/\s*Quiz$/i', '', $title));
        if ($topic === '') { $topic = $categoryName; }
        $cat = strtolower($categoryName);
        $desc = strtolower($description);
        $wantAddition = preg_match('/addition/i', $desc);
        $wantSubtraction = preg_match('/subtraction/i', $desc);
        $wantMultiplication = preg_match('/multiplication/i', $desc);
        $wantDivision = preg_match('/division/i', $desc);
        $wantShapes = preg_match('/shape|geometry|triangle|square|circle/i', $desc);
        $wantMeasure = preg_match('/measurement|measure|length|weight|volume/i', $desc);
        $avoidOps = (preg_match('/addition|subtraction|multiplication|division/i', $desc) && preg_match('/don\'t|avoid|exclude|without/i', $desc));

        $types = [];
        $mc = max(1, intval(round($count * 0.6)));
        $tf = max(0, intval(round($count * 0.2)));
        $sa = max(0, $count - $mc - $tf);
        for ($i=0; $i<$mc; $i++) $types[] = 'multiple_choice';
        for ($i=0; $i<$tf; $i++) $types[] = 'true_false';
        for ($i=0; $i<$sa; $i++) $types[] = 'short_answer';
        while (count($types) < $count) { $types[] = 'multiple_choice'; }
        if (count($types) > $count) { $types = array_slice($types, 0, $count); }

        $idx = 0;
        foreach ($types as $t) {
            if (strpos($cat, 'math') !== false) {
                if ($wantAddition || $wantSubtraction || $wantMultiplication || $wantDivision) {
                    if ($t === 'true_false') {
                        $q = 'True or False: ' . ($wantAddition ? '5 + 3 = 8' : ($wantSubtraction ? '10 - 4 = 6' : ($wantMultiplication ? '3 × 2 = 6' : '12 ÷ 3 = 4'))) . '.';
                        $correct = 0;
                        $questions[] = ['text'=>$q,'type'=>'true_false','options'=>['True','False','Not sure','Depends'],'correctAnswer'=>$correct];
                    } elseif ($t === 'short_answer') {
                        $q = 'Short answer: Solve: ' . ($wantAddition ? '7 + 5 =' : ($wantSubtraction ? '15 - 8 =' : ($wantMultiplication ? '4 × 6 =' : '20 ÷ 5 =')));
                        $questions[] = ['text'=>$q,'type'=>'short_answer','options'=>[$wantAddition ? '12' : ($wantSubtraction ? '7' : ($wantMultiplication ? '24' : '4')),'Wrong','Another answer','Unsure'],'correctAnswer'=>0];
                    } else {
                        $q = 'Which is correct?';
                        $opts = [];
                        if ($wantAddition) {
                            $opts = ['6 + 4 = 10', '6 + 4 = 11', '6 + 4 = 9', '6 + 4 = 12'];
                        } elseif ($wantSubtraction) {
                            $opts = ['12 - 5 = 7', '12 - 5 = 8', '12 - 5 = 6', '12 - 5 = 5'];
                        } elseif ($wantMultiplication) {
                            $opts = ['5 × 3 = 15', '5 × 3 = 14', '5 × 3 = 16', '5 × 3 = 12'];
                        } else {
                            $opts = ['24 ÷ 6 = 4', '24 ÷ 6 = 5', '24 ÷ 6 = 3', '24 ÷ 6 = 6'];
                        }
                        $questions[] = ['text'=>$q,'type'=>'multiple_choice','options'=>$opts,'correctAnswer'=>0];
                    }
                } elseif ($wantShapes || $wantMeasure) {
                    if ($wantShapes) {
                        if ($t === 'true_false') {
                            $questions[] = ['text'=>'True or False: A square has 4 equal sides.','type'=>'true_false','options'=>['True','False','Not sure','Depends'],'correctAnswer'=>0];
                        } elseif ($t === 'short_answer') {
                            $questions[] = ['text'=>'Name one 3D shape.','type'=>'short_answer','options'=>['Cube','Sphere','Pyramid','Prism'],'correctAnswer'=>0];
                        } else {
                            $questions[] = ['text'=>'Which shape has 3 sides?','type'=>'multiple_choice','options'=>['Triangle','Square','Circle','Rectangle'],'correctAnswer'=>0];
                        }
                    } else {
                        if ($t === 'true_false') {
                            $questions[] = ['text'=>'True or False: A meter is used to measure length.','type'=>'true_false','options'=>['True','False','Not sure','Depends'],'correctAnswer'=>0];
                        } elseif ($t === 'short_answer') {
                            $questions[] = ['text'=>'Name a unit of length.','type'=>'short_answer','options'=>['Meter','Kilogram','Liter','Celsius'],'correctAnswer'=>0];
                        } else {
                            $questions[] = ['text'=>'Which unit is used to measure weight?','type'=>'multiple_choice','options'=>['Kilogram','Meter','Liter','Celsius'],'correctAnswer'=>0];
                        }
                    }
                } else {
                if ($t === 'true_false') {
                    $qt = 'True or False: A square has 4 equal sides.';
                    $questions[] = ['text'=>$qt,'type'=>'true_false','options'=>['True','False','Not sure','Depends'],'correctAnswer'=>0];
                } elseif ($t === 'short_answer') {
                    $qt = 'Name one 3D shape.';
                    $questions[] = ['text'=>$qt,'type'=>'short_answer','options'=>['Cube','Sphere','Pyramid','Prism'],'correctAnswer'=>0];
                } else {
                    if ($avoidOps) {
                        $qt = 'Which shape has 3 sides?';
                        $questions[] = ['text'=>$qt,'type'=>'multiple_choice','options'=>['Triangle','Square','Circle','Rectangle'],'correctAnswer'=>0];
                    } else {
                        $qt = 'Which unit is commonly used to measure length?';
                        $questions[] = ['text'=>$qt,'type'=>'multiple_choice','options'=>['Meter','Kilogram','Liter','Celsius'],'correctAnswer'=>0];
                    }
                }
                }
            } elseif (strpos($cat, 'science') !== false) {
                $tpc = $topic ?: 'Science';
                if ($t === 'true_false') {
                    $qt = 'True or False: The Sun is a star.';
                    $questions[] = ['text'=>$qt,'type'=>'true_false','options'=>['True','False','Not sure','Depends'],'correctAnswer'=>0];
                } elseif ($t === 'short_answer') {
                    $qt = 'Name the process plants use to make food.';
                    $questions[] = ['text'=>$qt,'type'=>'short_answer','options'=>['Photosynthesis','Digestion','Respiration','Condensation'],'correctAnswer'=>0];
                } else {
                    $qt = 'Which part of a plant primarily absorbs water from the soil?';
                    $questions[] = ['text'=>$qt,'type'=>'multiple_choice','options'=>['Roots','Leaves','Flower','Stem'],'correctAnswer'=>0];
                }
            } elseif (strpos($cat, 'english') !== false) {
                if ($t === 'true_false') {
                    $qt = 'True or False: A noun names a person, place, or thing.';
                    $questions[] = ['text'=>$qt,'type'=>'true_false','options'=>['True','False','Not sure','Depends'],'correctAnswer'=>0];
                } elseif ($t === 'short_answer') {
                    $qt = 'Give an example of an adjective.';
                    $questions[] = ['text'=>$qt,'type'=>'short_answer','options'=>['Blue','Run','Quickly','Because'],'correctAnswer'=>0];
                } else {
                    $qt = 'Which word is a verb?';
                    $questions[] = ['text'=>$qt,'type'=>'multiple_choice','options'=>['Jump','Book','Happy','Blue'],'correctAnswer'=>0];
                }
            } else {
                if ($t === 'true_false') {
                    $qt = 'True or False: The topic relates to the selected category.';
                    $questions[] = ['text'=>$qt,'type'=>'true_false','options'=>['True','False','Not sure','Depends'],'correctAnswer'=>0];
                } elseif ($t === 'short_answer') {
                    $qt = 'Provide a brief fact about ' . $topic . '.';
                    $questions[] = ['text'=>$qt,'type'=>'short_answer','options'=>['A relevant fact','An unrelated idea','A vague phrase','A long off-topic text'],'correctAnswer'=>0];
                } else {
                    $qt = 'Which statement about ' . $topic . ' is correct?';
                    $questions[] = ['text'=>$qt,'type'=>'multiple_choice','options'=>[$topic.' is related to '.$cat,$topic.' is a city in Europe',$topic.' is a musical instrument',$topic.' is a sports event'],'correctAnswer'=>0];
                }
            }
            $idx++;
        }
        return $questions;
    }

    private function saveGeneratedQuiz() {
        header('Content-Type: application/json');
        @ini_set('display_errors', 0);
        @ini_set('html_errors', 0);
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
                return;
            }

            if (!isset($_SESSION['last_save_request'])) { $_SESSION['last_save_request'] = 0; }
            if (time() - intval($_SESSION['last_save_request']) < 5) {
                echo json_encode(['success' => false, 'error' => 'Please wait 5 seconds between requests']);
                return;
            }
            $_SESSION['last_save_request'] = time();

            $title = filter_var($_POST['title'] ?? '', FILTER_SANITIZE_STRING);
            $category = filter_var($_POST['category'] ?? '', FILTER_SANITIZE_STRING);
            $gradeLevel = filter_var($_POST['gradeLevel'] ?? '', FILTER_SANITIZE_STRING);
            $description = filter_var($_POST['description'] ?? '', FILTER_SANITIZE_STRING);
            $status = filter_var($_POST['status'] ?? 'draft', FILTER_SANITIZE_STRING);
            $passingGrade = filter_var($_POST['passing_grade'] ?? '70', FILTER_SANITIZE_STRING);
            $timeLimit = filter_var($_POST['time_limit'] ?? '0', FILTER_VALIDATE_INT);
            if ($timeLimit === false) { $timeLimit = 0; }

            if (empty($title) || empty($category) || empty($gradeLevel)) {
                echo json_encode(['success' => false, 'error' => 'Title, category, and grade level are required']);
                return;
            }

            $questions = [];
            if (isset($_POST['questions']) && is_array($_POST['questions'])) {
                $order = 0;
                foreach ($_POST['questions'] as $q) {
                    $text = filter_var($q['text'] ?? '', FILTER_SANITIZE_STRING);
                    if ($text === '') { continue; }
                    $question = new Question(null, null, $text, $order);
                    $opts = [];
                    if (isset($q['options']) && is_array($q['options'])) {
                        $idx = 0;
                        foreach ($q['options'] as $opt) {
                            $optText = filter_var($opt['text'] ?? '', FILTER_SANITIZE_STRING);
                            if ($optText === '') { continue; }
                            $label = chr(65 + $idx);
                            $isCorrect = isset($opt['is_correct']) && ($opt['is_correct'] === '1' || $opt['is_correct'] === 1);
                            $opts[] = new QuestionOption(null, null, $label, $optText, $isCorrect);
                            $idx++;
                        }
                    }
                    $questions[] = ['question' => $question, 'options' => $opts];
                    $order++;
                }
            }

            $quiz = new Quiz(
                null,
                $title,
                $category,
                $passingGrade,
                $description,
                $status,
                $gradeLevel,
                $timeLimit
            );

            $quizId = $this->quizModel->createQuiz($quiz, $questions);
            echo json_encode(['success' => true, 'quiz_id' => $quizId]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function aiStatus() {
        header('Content-Type: application/json');
        @ini_set('display_errors', 0);
        @ini_set('html_errors', 0);
        try {
            $configPath = __DIR__ . '/../ai_config.php';
            if (!file_exists($configPath)) {
                echo json_encode(['ok' => false, 'error' => 'ai_config.php missing']);
                return;
            }
            require_once $configPath;
            $provider = defined('AI_PROVIDER') ? AI_PROVIDER : '';
            $apiKey = defined('AI_API_KEY') ? AI_API_KEY : '';
            $baseUrl = defined('AI_BASE_URL') ? AI_BASE_URL : '';
            $status = ['ok' => true, 'provider' => $provider, 'baseUrl' => $baseUrl, 'hasKey' => !empty($apiKey)];

            if (empty($apiKey)) {
                echo json_encode(['ok' => false, 'error' => 'API key not set', 'provider' => $provider]);
                return;
            }

            if ($provider === 'deepseek') {
                // Try listing models (OpenAI-compatible)
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $baseUrl . '/v1/models',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey]
                ]);
                $resp = curl_exec($ch);
                $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                $status['http'] = $http;
                if ($http === 200) {
                    $status['api'] = 'reachable';
                } else {
                    $status['api'] = 'error';
                    $status['response'] = $resp;
                }
            } else {
                // Gemini simple ping via models list not implemented here
                $status['note'] = 'Gemini status limited; ensure key is valid.';
            }
            echo json_encode($status);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
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
