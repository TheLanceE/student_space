<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/AI.php';

class AIFeedbackController {
    public static function handle($pdo) {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'generate':
                self::generateFeedback($pdo);
                break;
            case 'view':
                self::viewFeedback($pdo);
                break;
            case 'save':
                self::saveFeedback($pdo);
                break;
            case 'dashboard':
                self::showDashboard($pdo);
                break;
            default:
                self::showDashboard($pdo);
        }
    }
    
    /**
     * Get student by ID
     */
    private static function getStudentByID($pdo, $studentId) {
        $stmt = $pdo->prepare("SELECT id, name, email, points, role FROM users WHERE id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Generate AI feedback
     */
    private static function generateFeedback($pdo) {
        $studentId = (int)($_GET['student_id'] ?? $_POST['student_id'] ?? 0);
        
        if ($studentId <= 0) {
            $_SESSION['error'] = 'Invalid student ID';
            self::redirectBack();
            return;
        }
        
        // Get student info
        $student = self::getStudentByID($pdo, $studentId);
        
        if (!$student) {
            $_SESSION['error'] = 'Student not found';
            self::redirectBack();
            return;
        }
        
        // Generate AI feedback
        $aiFeedback = AI::generateStudentFeedback($pdo, $studentId);
        
        // Store in session
        $_SESSION['ai_feedback'] = [
            'student' => $student,
            'feedback' => $aiFeedback['feedback'],
            'pattern' => $aiFeedback['pattern'],
            'confidence' => $aiFeedback['confidence'],
            'suggestions' => $aiFeedback['suggestions'],
            'stats' => $aiFeedback['stats']
        ];
        
        $_SESSION['success'] = 'AI feedback generated!';
        
        // Store for saving
        $_SESSION['generated_feedback_data'] = [
            'student_id' => $studentId,
            'feedback_text' => $aiFeedback['feedback'],
            'pattern_type' => $aiFeedback['pattern'],
            'confidence_score' => $aiFeedback['confidence']
        ];
        
        self::redirectBack();
    }
    
    /**
     * View feedback
     */
    private static function viewFeedback($pdo) {
        if (!isset($_SESSION['ai_feedback'])) {
            $_SESSION['error'] = 'No feedback generated yet';
            self::redirectBack();
            return;
        }
        
        self::redirectBack('view_feedback=1');
    }
    
    /**
     * Save feedback
     */
    private static function saveFeedback($pdo) {
        if (!isset($_SESSION['generated_feedback_data'])) {
            $_SESSION['error'] = 'No feedback to save';
            self::redirectBack();
            return;
        }
        
        $data = $_SESSION['generated_feedback_data'];
        $adminId = $_SESSION['userID'] ?? 1;
        $notes = $_POST['notes'] ?? 'No additional notes';
        
        try {
            // Create table if not exists
            $stmt = $pdo->prepare("
                CREATE TABLE IF NOT EXISTS feedback_saved (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    student_id INT NOT NULL,
                    admin_id INT NOT NULL,
                    feedback_text TEXT NOT NULL,
                    pattern_type VARCHAR(50),
                    confidence_score DECIMAL(3,2),
                    admin_notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            $stmt->execute();
            
            // Save feedback
            $stmt = $pdo->prepare("
                INSERT INTO feedback_saved 
                (student_id, admin_id, feedback_text, pattern_type, confidence_score, admin_notes) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['student_id'],
                $adminId,
                $data['feedback_text'],
                $data['pattern_type'],
                $data['confidence_score'],
                $notes
            ]);
            
            $_SESSION['success'] = 'Feedback saved!';
            unset($_SESSION['generated_feedback_data']);
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error saving feedback';
        }
        
        self::redirectBack();
    }
    
    /**
     * Show dashboard
     */
    private static function showDashboard($pdo) {
        // Get students
        $students = AI::getStudentsForFeedback($pdo);
        $_SESSION['ai_students'] = $students;
        
        // Get saved feedback
        try {
            $stmt = $pdo->prepare("
                SELECT fs.*, u.name as student_name
                FROM feedback_saved fs
                JOIN users u ON fs.student_id = u.id
                ORDER BY fs.created_at DESC
                LIMIT 10
            ");
            $stmt->execute();
            $_SESSION['saved_feedback'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $_SESSION['saved_feedback'] = [];
        }
        
        self::redirectBack('ai_dashboard=1');
    }
    
    /**
     * Redirect back
     */
    private static function redirectBack($query = '') {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
        if (!empty($referer)) {
            $url = $referer;
        } else {
            $url = '../Views/admin-back-office/AI.php';
        }
        
        if (!empty($query)) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . $query;
        }
        
        header('Location: ' . $url);
        exit;
    }
}

AIFeedbackController::handle($pdo);
?>