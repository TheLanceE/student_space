<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../Models/QuizReport.php';

class QuizReportController
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            global $db_connection;
            $this->pdo = $db_connection;
        } else {
            $this->pdo = $pdo;
        }
    }

    public static function validateCsrfFromPost(): bool
    {
        $posted = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (!is_string($posted) || $posted === '' || !is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $posted);
    }

    public function submitStudentReport(string $studentId): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request'];
        }

        if (!self::validateCsrfFromPost()) {
            return ['success' => false, 'error' => 'Invalid CSRF token'];
        }

        $quizId = trim((string)($_POST['quizId'] ?? ''));
        $questionId = trim((string)($_POST['questionId'] ?? ''));
        $reportType = trim((string)($_POST['reportType'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));

        if ($quizId === '' || $questionId === '' || $reportType === '' || $description === '') {
            return ['success' => false, 'error' => 'Missing required fields'];
        }

        $allowed = ['incorrect_answer', 'wrong_display', 'typo', 'other'];
        if (!in_array($reportType, $allowed, true)) {
            $reportType = 'other';
        }

        $ok = QuizReport::create($this->pdo, [
            'id' => 'qreport_' . bin2hex(random_bytes(8)),
            'quizId' => $quizId,
            'questionId' => $questionId,
            'reportedBy' => $studentId,
            'reportType' => $reportType,
            'description' => $description,
            'status' => 'pending',
        ]);

        return ['success' => $ok, 'error' => $ok ? null : 'Failed to submit report'];
    }

    public function listForTeacher(string $teacherId, string $status): array
    {
        return QuizReport::listForTeacher($this->pdo, $teacherId, $status);
    }

    public function listAllForAdmin(string $status): array
    {
        return QuizReport::listAll($this->pdo, $status);
    }

    public function updateStatusForTeacher(string $teacherId, string $reportId, string $status): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request'];
        }

        if (!self::validateCsrfFromPost()) {
            return ['success' => false, 'error' => 'Invalid CSRF token'];
        }

        $allowed = ['pending', 'reviewed', 'resolved', 'dismissed'];
        if (!in_array($status, $allowed, true)) {
            return ['success' => false, 'error' => 'Invalid status'];
        }

        $ok = QuizReport::updateStatus($this->pdo, $reportId, $status, $teacherId);
        return ['success' => $ok, 'error' => $ok ? null : 'Failed to update status'];
    }

    public function updateStatusForAdmin(string $adminId, string $reportId, string $status): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request'];
        }

        if (!self::validateCsrfFromPost()) {
            return ['success' => false, 'error' => 'Invalid CSRF token'];
        }

        $allowed = ['pending', 'reviewed', 'resolved', 'dismissed'];
        if (!in_array($status, $allowed, true)) {
            return ['success' => false, 'error' => 'Invalid status'];
        }

        $ok = QuizReport::updateStatusAdmin($this->pdo, $reportId, $status, $adminId);
        return ['success' => $ok, 'error' => $ok ? null : 'Failed to update status'];
    }
}

// API endpoint handler (direct access only)
if (basename(__FILE__) === basename((string)($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    header('Content-Type: application/json');

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = [];
    }

    // Merge into $_POST so controller methods (which read $_POST) work as-is
    foreach ($input as $k => $v) {
        if (is_string($k) && $k !== '' && !isset($_POST[$k])) {
            $_POST[$k] = $v;
        }
    }

    $action = (string)($input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '');

    // Lightweight auth check (JSON-safe)
    $role = (string)($_SESSION['user']['role'] ?? $_SESSION['role'] ?? '');
    $studentId = (string)($_SESSION['user']['id'] ?? $_SESSION['student_id'] ?? $_SESSION['user_id'] ?? '');
    if ($studentId === '' || $role === '') {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }

    $controller = new QuizReportController();

    switch ($action) {
        case 'submit_student_report':
            if ($role !== 'student') {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                exit;
            }
            echo json_encode($controller->submitStudentReport($studentId));
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            break;
    }
}
