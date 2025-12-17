<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/AIPredictions.php';

class AIPredictionsController {
    public static function handle($pdo) {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'predict_student':
                self::predictStudent($pdo);
                break;
            case 'predict_all':
                self::predictAllStudents($pdo);
                break;
            case 'student_view':
                self::studentView($pdo);
                break;
            case 'admin_view':
                self::adminView($pdo);
                break;
            default:
                self::adminView($pdo);
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
     * Predict for a student
     */
    private static function predictStudent($pdo) {
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
        
        // Generate predictions
        $predictions = AIPredictions::predictEndOfMonth($pdo, $studentId);
        
        // Store in session
        $_SESSION['ai_predictions'] = [
            'student' => $student,
            'predictions' => $predictions,
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
        $_SESSION['success'] = 'AI predictions generated!';
        
        self::redirectBack('view_predictions=1&student_id=' . $studentId);
    }
    
    /**
     * Predict for all students
     */
    private static function predictAllStudents($pdo) {
        $allPredictions = AIPredictions::getAllStudentPredictions($pdo);
        $_SESSION['all_ai_predictions'] = $allPredictions;
        $_SESSION['success'] = 'AI predictions generated for all students!';
        self::redirectBack('view_all_predictions=1');
    }
    
    /**
     * Student view
     */
    private static function studentView($pdo) {
        $studentId = $_SESSION['userID'] ?? 1;
        
        if ($studentId <= 0) {
            $_SESSION['error'] = 'Please login to view predictions';
            self::redirectBack();
            return;
        }
        
        // Generate predictions
        $predictions = AIPredictions::predictEndOfMonth($pdo, $studentId);
        $student = self::getStudentByID($pdo, $studentId);
        
        // Store in session
        $_SESSION['student_predictions'] = [
            'student' => $student,
            'predictions' => $predictions,
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
        self::redirectBack('student_predictions=1');
    }
    
    /**
     * Admin view
     */
    private static function adminView($pdo) {
        $allPredictions = AIPredictions::getAllStudentPredictions($pdo);
        
        // Calculate stats
        $stats = self::calculatePredictionStats($allPredictions);
        
        $_SESSION['predictions_dashboard'] = [
            'all_predictions' => $allPredictions,
            'stats' => $stats,
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
        self::redirectBack('predictions_dashboard=1');
    }
    
    /**
     * Calculate stats
     */
    private static function calculatePredictionStats($predictions) {
        if (empty($predictions)) return [];
        
        $totalStudents = count($predictions);
        $totalPredictedPoints = 0;
        $confidenceSum = 0;
        
        foreach ($predictions as $prediction) {
            $totalPredictedPoints += $prediction['points_prediction'];
            $confidenceSum += $prediction['confidence'];
        }
        
        return [
            'total_students' => $totalStudents,
            'average_predicted_points' => $totalStudents > 0 ? round($totalPredictedPoints / $totalStudents) : 0,
            'average_confidence' => $totalStudents > 0 ? round($confidenceSum / $totalStudents) : 0
        ];
    }
    
    /**
     * Redirect back
     */
    private static function redirectBack($query = '') {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
        if (!empty($referer)) {
            $url = $referer;
        } else {
            $role = $_SESSION['role'] ?? 'admin';
            if ($role == 'admin') {
                $url = '../Views/admin-back-office/AI.php';
            } else {
                $url = '../Views/front-office/AI.php';
            }
        }
        
        if (!empty($query)) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . $query;
        }
        
        header('Location: ' . $url);
        exit;
    }
}

AIPredictionsController::handle($pdo);
?>