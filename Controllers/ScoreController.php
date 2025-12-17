<?php
/**
 * ScoreController - Handles quiz score submission and retrieval
 * Manages student quiz attempts, scoring, and leaderboards
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../Models/Quiz.php';

/**
 * Controller for quiz scoring operations
 */
final class ScoreController
{
    /** @var PDO Database connection */
    private PDO $pdo;

    /**
     * Constructor
     * @param PDO|null $pdo Optional PDO connection
     */
    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            global $db_connection;
            $this->pdo = $db_connection;
        } else {
            $this->pdo = $pdo;
        }
    }

    public static function getSessionUser(): ?array
    {
        if (isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['id'])) {
            return [
                'id' => (string)($_SESSION['user']['id'] ?? ''),
                'role' => (string)($_SESSION['user']['role'] ?? ''),
                'username' => (string)($_SESSION['user']['username'] ?? ''),
            ];
        }

        if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
            return [
                'id' => (string)$_SESSION['user_id'],
                'role' => (string)$_SESSION['role'],
                'username' => (string)($_SESSION['username'] ?? ''),
            ];
        }

        $role = (string)($_SESSION['role'] ?? '');
        if ($role === 'student' && isset($_SESSION['student_id'])) {
            return ['id' => (string)$_SESSION['student_id'], 'role' => 'student', 'username' => (string)($_SESSION['username'] ?? '')];
        }

        return null;
    }

    public static function requireStudentJson(): array
    {
        $user = self::getSessionUser();
        if (!$user || $user['id'] === '' || $user['role'] === '') {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }
        if ($user['role'] !== 'student') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden']);
            exit;
        }
        return $user;
    }

    public static function validateCsrfJson(array $input): bool
    {
        $posted = $input['csrf_token'] ?? ($_POST['csrf_token'] ?? '');
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $token = (is_string($header) && $header !== '') ? $header : $posted;
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (!is_string($token) || $token === '' || !is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public function submitQuizAttempt(array $input, array $user): array
    {
        $quizId = trim((string)($input['quizId'] ?? ''));
        if ($quizId === '') {
            return ['success' => false, 'error' => 'Missing quizId'];
        }

        $quiz = Quiz::getById($this->pdo, $quizId);
        if (!$quiz) {
            return ['success' => false, 'error' => 'Quiz not found'];
        }

        $questions = $quiz['questions_decoded'] ?? [];
        if (!is_array($questions)) {
            $questions = [];
        }

        $answers = $input['answers'] ?? [];
        if (!is_array($answers)) {
            $answers = [];
        }

        $total = 0;
        $score = 0;
        $feedback = [];

        foreach ($questions as $q) {
            $qid = (string)($q['id'] ?? '');
            $opts = $q['options'] ?? [];
            $correctIndex = (int)($q['correctIndex'] ?? -1);
            if ($qid === '' || !is_array($opts) || count($opts) < 2) {
                continue;
            }

            $total++;
            $chosenIdx = -1;
            if (array_key_exists($qid, $answers)) {
                $chosenIdx = (int)$answers[$qid];
            }

            $isCorrect = ($chosenIdx === $correctIndex);
            if ($isCorrect) {
                $score++;
            }

            $feedback[$qid] = [
                'chosenIdx' => $chosenIdx,
                'correctIdx' => $correctIndex,
                'isCorrect' => $isCorrect,
            ];
        }

        if ($total === 0) {
            return ['success' => false, 'error' => 'Quiz has no questions'];
        }

        $durationSec = (int)($input['durationSec'] ?? 0);
        if ($durationSec < 0) {
            $durationSec = 0;
        }

        $attemptStmt = $this->pdo->prepare("SELECT COUNT(*) FROM scores WHERE userId = ? AND quizId = ? AND type = 'quiz'");
        $attemptStmt->execute([$user['id'], $quizId]);
        $attempt = (int)$attemptStmt->fetchColumn() + 1;

        $scoreId = 'sc_' . bin2hex(random_bytes(8));

        $insert = $this->pdo->prepare(
            "INSERT INTO scores (id, userId, username, courseId, quizId, score, total, durationSec, attempt, type, timestamp)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'quiz', NOW())"
        );

        $ok = $insert->execute([
            $scoreId,
            $user['id'],
            $user['username'],
            (string)($quiz['courseId'] ?? ''),
            $quizId,
            $score,
            $total,
            $durationSec,
            $attempt,
        ]);

        if (!$ok) {
            return ['success' => false, 'error' => 'Failed to save score'];
        }

        return [
            'success' => true,
            'record' => [
                'id' => $scoreId,
                'userId' => $user['id'],
                'username' => $user['username'],
                'courseId' => (string)($quiz['courseId'] ?? ''),
                'quizId' => $quizId,
                'score' => $score,
                'total' => $total,
                'durationSec' => $durationSec,
                'attempt' => $attempt,
                'type' => 'quiz',
            ],
            'feedback' => $feedback,
        ];
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

    $action = (string)($input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '');
    $user = ScoreController::requireStudentJson();

    $csrfRequired = ['submit_quiz_attempt'];
    if (in_array($action, $csrfRequired, true) && !ScoreController::validateCsrfJson($input)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }

    $controller = new ScoreController();

    switch ($action) {
        case 'submit_quiz_attempt':
            echo json_encode($controller->submitQuizAttempt($input, $user));
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
            break;
    }
}
