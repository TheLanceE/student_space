<?php
require_once __DIR__ . '/Quiz.php';
require_once __DIR__ . '/Question.php';
require_once __DIR__ . '/QuestionOption.php';
require_once __DIR__ . '/../config.php';

class QuizModel {
    private $db;
    private $tables = [];
    
    public function __construct() {
        $this->db = config::getConnexion();
        $this->tables = [
            'questions' => $this->resolve(['quiz_questions','questions']),
            'options' => $this->resolve(['question_options','options']),
            'attempts' => $this->resolve(['quiz_attempts','attempts']),
            'attempt_answers' => $this->resolve(['quiz_attempt_answers','attempt_answers'])
        ];
    }

    private function hasColumn($table, $column) {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM `" . $table . "` LIKE ?");
            $stmt->execute([$column]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function resolve($candidates) {
        foreach ($candidates as $name) {
            try {
                $stmt = $this->db->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$name]);
                if ($stmt->rowCount() > 0) { return $name; }
            } catch (Exception $e) {}
        }
        return $candidates[0];
    }

    private function t($key) { return $this->tables[$key] ?? $key; }
    
    // Create a new quiz
    public function createQuiz($quiz, $questions = []) {
        $this->db->beginTransaction();
        
        try {
            // Insert quiz (handle missing teacher_id column)
            $hasTeacherId = $this->hasColumn('quizzes', 'teacher_id');
            if ($hasTeacherId) {
                $stmt = $this->db->prepare("\n                    INSERT INTO quizzes (title, description, category, grade, status, teacher_id, passing_grade, time_limit)\n                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)\n                ");
                $stmt->execute([
                    $quiz->getTitle(),
                    $quiz->getDescription(),
                    $quiz->getCategory(),
                    $quiz->getGrade(),
                    $quiz->getStatus(),
                    $_SESSION['user_id'],
                    $quiz->getPassingGrade(),
                    $quiz->getTimeLimit()
                ]);
            } else {
                $stmt = $this->db->prepare("\n                    INSERT INTO quizzes (title, description, category, grade, status, passing_grade, time_limit)\n                    VALUES (?, ?, ?, ?, ?, ?, ?)\n                ");
                $stmt->execute([
                    $quiz->getTitle(),
                    $quiz->getDescription(),
                    $quiz->getCategory(),
                    $quiz->getGrade(),
                    $quiz->getStatus(),
                    $quiz->getPassingGrade(),
                    $quiz->getTimeLimit()
                ]);
            }
            
            $quizId = $this->db->lastInsertId();
            $quiz->setId($quizId);
            
            // Insert questions and options
            foreach ($questions as $questionData) {
                $question = $questionData['question'];
                $options = $questionData['options'];
                
                $stmt = $this->db->prepare("INSERT INTO " . $this->t('questions') . " (quiz_id, question_text, question_order) VALUES (?, ?, ?)");
                $stmt->execute([$quizId, $question->getQuestionText(), $question->getQuestionOrder()]);
                $questionId = $this->db->lastInsertId();
                
                foreach ($options as $option) {
                    $stmt = $this->db->prepare("INSERT INTO " . $this->t('options') . " (question_id, option_label, option_text, is_correct) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $questionId,
                        $option->getOptionLabel(),
                        $option->getOptionText(),
                        $option->getIsCorrect() ? 1 : 0
                    ]);
                }
            }
            
            $this->db->commit();
            return $quizId;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    // Get quizzes by teacher
    public function getQuizzesByTeacher($teacherId) {
        try {
            $hasTeacherId = $this->hasColumn('quizzes', 'teacher_id');
            if ($hasTeacherId) {
                $stmt = $this->db->prepare(
                    "SELECT q.*, "
                    . "(SELECT COUNT(*) FROM " . $this->t('attempts') . " WHERE quiz_id = q.id) as attempt_count, "
                    . "(SELECT COUNT(*) FROM " . $this->t('questions') . " WHERE quiz_id = q.id) as question_count "
                    . "FROM quizzes q "
                    . "WHERE q.teacher_id = ? "
                    . "ORDER BY q.created_at DESC"
                );
                $stmt->execute([$teacherId]);
            } else {
                $stmt = $this->db->query(
                    "SELECT q.*, "
                    . "(SELECT COUNT(*) FROM " . $this->t('attempts') . " WHERE quiz_id = q.id) as attempt_count, "
                    . "(SELECT COUNT(*) FROM " . $this->t('questions') . " WHERE quiz_id = q.id) as question_count "
                    . "FROM quizzes q "
                    . "ORDER BY q.created_at DESC"
                );
            }
            
            $quizzes = [];
            while ($row = $stmt->fetch()) {
                $quizzes[] = new Quiz(
                    $row['id'],
                    $row['title'],
                    $row['category'],
                    $row['passing_grade'],
                    $row['description'],
                    $row['status'] ?? 'draft', // Default to draft if status doesn't exist
                $row['grade'],
                    $row['time_limit'],
                    $row['created_at']
                );
            }
            
            return $quizzes;
        } catch (Exception $e) {
            error_log("Error in getQuizzesByTeacher: " . $e->getMessage());
            return [];
        }
    }
    
    // Get all active quizzes (FIXED - check if status column exists)
    public function getAllActiveQuizzes() {
        try {
            // First, check if status column exists
            $columnExists = false;
            try {
                $checkStmt = $this->db->query("SHOW COLUMNS FROM quizzes LIKE 'status'");
                $columnExists = $checkStmt->rowCount() > 0;
            } catch (Exception $e) {
                $columnExists = false;
            }
            
            if ($columnExists) {
                $stmt = $this->db->query("
                    SELECT q.*, '' as teacher_name
                    FROM quizzes q
                    WHERE q.status = 'active'
                    ORDER BY q.created_at DESC
                ");
            } else {
                // If status column doesn't exist, get all quizzes
                $stmt = $this->db->query("
                    SELECT q.*, '' as teacher_name
                    FROM quizzes q
                    ORDER BY q.created_at DESC
                ");
            }
            
            $quizzes = [];
            while ($row = $stmt->fetch()) {
                $quiz = new Quiz(
                    $row['id'],
                    $row['title'],
                    $row['category'],
                    $row['passing_grade'],
                    $row['description'],
                    $row['status'] ?? 'active', // Default if column doesn't exist
                    $row['grade'],
                    $row['time_limit'],
                    $row['created_at']
                );
                $quizzes[] = [
                    'quiz' => $quiz,
                    'teacher_name' => $row['teacher_name']
                ];
            }
            
            return $quizzes;
            
        } catch (Exception $e) {
            error_log("Error in getAllActiveQuizzes: " . $e->getMessage());
            return [];
        }
    }

    public function updateQuiz($quizId, $quiz, $questions = []) {
        $this->db->beginTransaction();
        try {
            $hasTeacherId = $this->hasColumn('quizzes', 'teacher_id');
            if ($hasTeacherId) {
                $stmt = $this->db->prepare("UPDATE quizzes SET title=?, description=?, category=?, grade=?, status=?, passing_grade=?, time_limit=? WHERE id=? AND teacher_id=?");
                $stmt->execute([
                    $quiz->getTitle(),
                    $quiz->getDescription(),
                    $quiz->getCategory(),
                    $quiz->getGrade(),
                    $quiz->getStatus(),
                    $quiz->getPassingGrade(),
                    $quiz->getTimeLimit(),
                    $quizId,
                    $_SESSION['user_id']
                ]);
            } else {
                $stmt = $this->db->prepare("UPDATE quizzes SET title=?, description=?, category=?, grade=?, status=?, passing_grade=?, time_limit=? WHERE id=?");
                $stmt->execute([
                    $quiz->getTitle(),
                    $quiz->getDescription(),
                    $quiz->getCategory(),
                    $quiz->getGrade(),
                    $quiz->getStatus(),
                    $quiz->getPassingGrade(),
                    $quiz->getTimeLimit(),
                    $quizId
                ]);
            }

            // Remove existing questions/options
            $qStmt = $this->db->prepare("SELECT id FROM " . $this->t('questions') . " WHERE quiz_id = ?");
            $qStmt->execute([$quizId]);
            $ids = $qStmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($ids)) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $delOpt = $this->db->prepare("DELETE FROM " . $this->t('options') . " WHERE question_id IN ($in)");
                $delOpt->execute($ids);
            }
            $delQ = $this->db->prepare("DELETE FROM " . $this->t('questions') . " WHERE quiz_id = ?");
            $delQ->execute([$quizId]);

            // Insert new questions/options
            $order = 0;
            foreach ($questions as $qd) {
                $question = $qd['question'];
                $options = $qd['options'];
                $stmt = $this->db->prepare("INSERT INTO " . $this->t('questions') . " (quiz_id, question_text, question_order) VALUES (?, ?, ?)");
                $stmt->execute([$quizId, $question->getQuestionText(), $order]);
                $questionId = $this->db->lastInsertId();
                foreach ($options as $option) {
                    $oStmt = $this->db->prepare("INSERT INTO " . $this->t('options') . " (question_id, option_label, option_text, is_correct) VALUES (?, ?, ?, ?)");
                    $oStmt->execute([$questionId, $option->getOptionLabel(), $option->getOptionText(), $option->getIsCorrect() ? 1 : 0]);
                }
                $order++;
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Quizzes for Browse tab (robust to missing teacher_id/status)
    public function getQuizzesForBrowse() {
        try {
            $hasStatus = false;
            try {
                $checkStmt = $this->db->query("SHOW COLUMNS FROM quizzes LIKE 'status'");
                $hasStatus = $checkStmt->rowCount() > 0;
            } catch (Exception $e) { $hasStatus = false; }

            $hasTeacherId = false;
            try {
                $checkStmt2 = $this->db->query("SHOW COLUMNS FROM quizzes LIKE 'teacher_id'");
                $hasTeacherId = $checkStmt2->rowCount() > 0;
            } catch (Exception $e) { $hasTeacherId = false; }

            $sql = "SELECT q.*, '' as teacher_name FROM quizzes q";
            if ($hasStatus) { $sql .= " WHERE q.status = 'active'"; }
            $sql .= " ORDER BY q.created_at DESC";

            $stmt = $this->db->query($sql);

            $result = [];
            while ($row = $stmt->fetch()) {
                $quiz = new Quiz(
                    $row['id'],
                    $row['title'],
                    $row['category'],
                    $row['passing_grade'],
                    $row['description'],
                    $row['status'] ?? 'active',
                    $row['grade'],
                    $row['time_limit'],
                    $row['created_at']
                );
                $result[] = [
                    'quiz' => $quiz,
                    'teacher_name' => (isset($row['teacher_name']) && $row['teacher_name'] !== '') ? $row['teacher_name'] : 'Teacher'
                ];
            }
            return $result;

        } catch (Exception $e) {
            error_log("Error in getQuizzesForBrowse: " . $e->getMessage());
            return [];
        }
    }
    
    // Get quiz by ID with questions and options
    public function getQuizById($quizId) {
        try {
            // Get quiz basic info
            $stmt = $this->db->prepare("
                SELECT q.*, '' as teacher_name 
                FROM quizzes q 
                WHERE q.id = ?
            ");
            $stmt->execute([$quizId]);
            $quizData = $stmt->fetch();
            
            if (!$quizData) {
                return null;
            }
            
            $quiz = new Quiz(
                $quizData['id'],
                $quizData['title'],
                $quizData['category'],
                $quizData['passing_grade'],
                $quizData['description'],
                $quizData['status'] ?? 'draft',
                $quizData['grade'],
                $quizData['time_limit'],
                $quizData['created_at']
            );
            
            // Get questions
            $stmt = $this->db->prepare("SELECT * FROM " . $this->t('questions') . " WHERE quiz_id = ? ORDER BY question_order ASC");
            $stmt->execute([$quizId]);
            $questions = [];
            
            while ($questionData = $stmt->fetch()) {
                $question = new Question(
                    $questionData['id'],
                    $questionData['quiz_id'],
                    $questionData['question_text'],
                    $questionData['question_order']
                );
                
                // Get options for this question
                $optionStmt = $this->db->prepare("SELECT * FROM " . $this->t('options') . " WHERE question_id = ? ORDER BY option_label ASC");
                $optionStmt->execute([$questionData['id']]);
                $options = [];
                
                while ($optionData = $optionStmt->fetch()) {
                    $options[] = new QuestionOption(
                        $optionData['id'],
                        $optionData['question_id'],
                        $optionData['option_label'],
                        $optionData['option_text'],
                        (bool)$optionData['is_correct']
                    );
                }
                
                $questions[] = [
                    'question' => $question,
                    'options' => $options
                ];
            }
            
            return [
                'quiz' => $quiz,
                'teacher_name' => $quizData['teacher_name'],
                'questions' => $questions
            ];
            
        } catch (Exception $e) {
            error_log("Error in getQuizById: " . $e->getMessage());
            return null;
        }
    }
    
    // Delete quiz
    public function deleteQuiz($quizId, $teacherId) {
        try {
            $hasTeacherId = $this->hasColumn('quizzes', 'teacher_id');
            if ($hasTeacherId) {
                $stmt = $this->db->prepare("\n                    DELETE FROM quizzes \n                    WHERE id = ? AND teacher_id = ?\n                ");
                return $stmt->execute([$quizId, $teacherId]);
            } else {
                $stmt = $this->db->prepare("\n                    DELETE FROM quizzes \n                    WHERE id = ?\n                ");
                return $stmt->execute([$quizId]);
            }
        } catch (Exception $e) {
            error_log("Error in deleteQuiz: " . $e->getMessage());
            return false;
        }
    }
    
    // Save quiz attempt
    public function saveQuizAttempt($quizId, $studentId, $answers) {
        $this->db->beginTransaction();
        
        try {
            // Get quiz questions to calculate score
            $quizData = $this->getQuizById($quizId);
            $totalQuestions = count($quizData['questions']);
            $correctAnswers = 0;
            
            foreach ($quizData['questions'] as $q) {
                $questionId = $q['question']->getId();
                $selectedOptionId = $answers[$questionId] ?? null;
                
                if ($selectedOptionId) {
                    // Check if selected option is correct
                    foreach ($q['options'] as $option) {
                        if ($option->getId() == $selectedOptionId && $option->getIsCorrect()) {
                            $correctAnswers++;
                            break;
                        }
                    }
                }
            }
            
            $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

            // Save attempt using available columns
            $attemptsTable = $this->t('attempts');
            $userIdColumn = $this->hasColumn($attemptsTable, 'student_id') ? 'student_id' : 'user_id';

            $cols = ['quiz_id', $userIdColumn, 'score'];
            $params = [$quizId, $studentId, $score];

            if ($this->hasColumn($attemptsTable, 'correct_answers')) {
                $cols[] = 'correct_answers';
                $params[] = $correctAnswers;
            }
            if ($this->hasColumn($attemptsTable, 'total_questions')) {
                $cols[] = 'total_questions';
                $params[] = $totalQuestions;
            }
            if ($this->hasColumn($attemptsTable, 'passed')) {
                $cols[] = 'passed';
                $params[] = ($score >= 70) ? 1 : 0;
            }
            if ($this->hasColumn($attemptsTable, 'completed_at')) {
                $cols[] = 'completed_at';
                $params[] = date('Y-m-d H:i:s');
            }

            $placeholders = implode(', ', array_fill(0, count($cols), '?'));
            $sql = "INSERT INTO `{$attemptsTable}` (" . implode(', ', $cols) . ") VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $attemptId = $this->db->lastInsertId();
            
            // Save attempt answers
            foreach ($answers as $questionId => $selectedOptionId) {
                // Determine correctness for this answer
                $isCorrectFlag = 0;
                foreach ($quizData['questions'] as $qq) {
                    if ($qq['question']->getId() == $questionId) {
                        foreach ($qq['options'] as $op) {
                            if ($op->getId() == $selectedOptionId && $op->getIsCorrect()) {
                                $isCorrectFlag = 1;
                                break 2;
                            }
                        }
                    }
                }

                $answersTable = $this->t('attempt_answers');
                if ($this->hasColumn($answersTable, 'is_correct')) {
                    $stmt = $this->db->prepare("INSERT INTO `{$answersTable}` (attempt_id, question_id, selected_option_id, is_correct) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$attemptId, $questionId, $selectedOptionId, $isCorrectFlag]);
                } else {
                    $stmt = $this->db->prepare("INSERT INTO `{$answersTable}` (attempt_id, question_id, selected_option_id) VALUES (?, ?, ?)");
                    $stmt->execute([$attemptId, $questionId, $selectedOptionId]);
                }
            }
            
            $this->db->commit();
            return [
                'attempt_id' => $attemptId,
                'score' => $score,
                'correct_answers' => $correctAnswers,
                'total_questions' => $totalQuestions
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    // Get student statistics
    public function getStudentStats($studentId) {
        try {
            $attemptsTable = $this->t('attempts');
            $userIdColumn = $this->hasColumn($attemptsTable, 'student_id') ? 'student_id' : 'user_id';
            // Count unique quizzes completed (not including retakes)
            $stmt = $this->db->prepare("SELECT COUNT(DISTINCT quiz_id) as completed_quizzes, AVG(score) as average_score, MAX(score) as best_score FROM `{$attemptsTable}` WHERE {$userIdColumn} = ?");
            $stmt->execute([$studentId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error in getStudentStats: " . $e->getMessage());
            return [
                'completed_quizzes' => 0,
                'average_score' => 0,
                'best_score' => 0
            ];
        }
    }

    // Get attempt with quiz, questions, options and the student's selections
    public function getAttemptWithDetails($attemptId, $userId) {
        try {
            $attemptsTable = $this->t('attempts');
            $answersTable = $this->t('attempt_answers');
            $userIdColumn = $this->hasColumn($attemptsTable, 'student_id') ? 'student_id' : 'user_id';

            // Verify the attempt belongs to the user
            $stmt = $this->db->prepare("SELECT * FROM `{$attemptsTable}` WHERE id = ? AND {$userIdColumn} = ?");
            $stmt->execute([$attemptId, $userId]);
            $attempt = $stmt->fetch();
            if (!$attempt) { return null; }

            $quizId = $attempt['quiz_id'];

            // Load student's answers for this attempt
            $ansStmt = $this->db->prepare("SELECT question_id, selected_option_id FROM `{$answersTable}` WHERE attempt_id = ?");
            $ansStmt->execute([$attemptId]);
            $selectedMap = [];
            while ($row = $ansStmt->fetch()) {
                $selectedMap[$row['question_id']] = $row['selected_option_id'];
            }

            // Load quiz with questions and options
            $quizData = $this->getQuizById($quizId);
            if (!$quizData) { return null; }

            // Attach selected option id to each question entry
            $questions = [];
            foreach ($quizData['questions'] as $q) {
                $qid = $q['question']->getId();
                $q['selected_option_id'] = isset($selectedMap[$qid]) ? intval($selectedMap[$qid]) : null;
                $questions[] = $q;
            }

            return [
                'attempt' => $attempt,
                'quiz' => $quizData['quiz'],
                'questions' => $questions
            ];
        } catch (Exception $e) {
            error_log("Error in getAttemptWithDetails: " . $e->getMessage());
            return null;
        }
    }

    // Get list of quiz IDs the student has attempted
    public function getAttemptedQuizIds($studentId) {
        try {
            $attemptsTable = $this->t('attempts');
            $userIdColumn = $this->hasColumn($attemptsTable, 'student_id') ? 'student_id' : 'user_id';
            $stmt = $this->db->prepare("SELECT DISTINCT quiz_id FROM `{$attemptsTable}` WHERE {$userIdColumn} = ?");
            $stmt->execute([$studentId]);
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $rows ? $rows : [];
        } catch (Exception $e) {
            error_log("Error in getAttemptedQuizIds: " . $e->getMessage());
            return [];
        }
    }
    
    // Check if database tables exist and create them if not
    public function initializeDatabase() {
        try {
            // This is a simple check - you might want to implement proper migration system
            $tables = ['quizzes', $this->t('questions'), $this->t('options'), $this->t('attempts'), $this->t('attempt_answers')];
            
            foreach ($tables as $table) {
                $stmt = $this->db->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                if ($stmt->rowCount() == 0) {
                    throw new Exception("Table $table does not exist. Please run the schema.sql file.");
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("Database initialization error: " . $e->getMessage());
            return false;
        }
    }
}
?>
