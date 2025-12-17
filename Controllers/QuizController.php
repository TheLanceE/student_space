<?php
/**
 * QuizController - Handles quiz creation, management, and submission
 * Provides CRUD operations for quizzes and quiz questions
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../Models/Quiz.php';

/**
 * Controller for quiz-related operations
 */
class QuizController
{
    /** @var PDO Database connection */
    private PDO $pdo;

    /**
     * Constructor
     * @param PDO|null $pdo Optional PDO connection (uses global if not provided)
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

    /**
     * Validate CSRF token from POST request
     * @return bool True if token is valid
     */
    public static function validateCsrfFromPost(): bool
    {
        $posted = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (!is_string($posted) || $posted === '' || !is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $posted);
    }

    /**
     * Create a new quiz from POST data
     * @param string $teacherId Teacher creating the quiz
     * @return array Success status and quiz ID or error
     */
    public function createFromPost(string $teacherId): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['success' => false, 'error' => 'Invalid request'];
        }

        if (!self::validateCsrfFromPost()) {
            return ['success' => false, 'error' => 'Invalid CSRF token'];
        }

        $courseId = trim((string)($_POST['courseId'] ?? ''));
        $title = trim((string)($_POST['title'] ?? ''));
        $durationSec = (int)($_POST['durationSec'] ?? 60);
        $difficulty = trim((string)($_POST['difficulty'] ?? ''));

        if ($courseId === '' || $title === '') {
            return ['success' => false, 'error' => 'Course and title are required'];
        }

        $questions = $_POST['questions'] ?? [];
        if (!is_array($questions) || count($questions) === 0) {
            return ['success' => false, 'error' => 'At least one question is required'];
        }

        $normalized = [];
        $idx = 0;
        foreach ($questions as $q) {
            $text = trim((string)($q['text'] ?? ''));
            $options = $q['options'] ?? [];
            $correctIndex = (int)($q['correctIndex'] ?? -1);

            if ($text === '' || !is_array($options) || count($options) < 2) {
                continue;
            }

            $options = array_values(array_map(fn($o) => trim((string)$o), $options));
            $options = array_values(array_filter($options, fn($o) => $o !== ''));
            if (count($options) < 2) {
                continue;
            }

            if ($correctIndex < 0 || $correctIndex >= count($options)) {
                $correctIndex = 0;
            }

            $normalized[] = [
                'id' => 'q_' . bin2hex(random_bytes(6)) . '_' . $idx,
                'text' => $text,
                'options' => $options,
                'correctIndex' => $correctIndex,
            ];
            $idx++;
        }

        if (count($normalized) === 0) {
            return ['success' => false, 'error' => 'No valid questions provided'];
        }

        $quizId = 'quiz_' . bin2hex(random_bytes(8));

        return Quiz::create($this->pdo, [
            'id' => $quizId,
            'courseId' => $courseId,
            'title' => $title,
            'durationSec' => max(30, $durationSec),
            'difficulty' => $difficulty !== '' ? $difficulty : null,
            'questions' => $normalized,
            'createdBy' => $teacherId,
        ]);
    }
}
