<?php
/**
 * ReportController - Handles student report CRUD operations
 * Creates, updates, and lists performance/behavioral/attendance reports
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../Models/Report.php';

/**
 * Controller for report management
 */
class ReportController
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

    public static function allowedStatuses(): array
    {
        return ['Pending', 'Reviewed', 'Kept'];
    }

    public static function allowedTypes(): array
    {
        return ['Performance', 'Behavioral', 'Attendance', 'Progress'];
    }

    public function listForRole(string $role, string $userId, ?string $status = null): array
    {
        if ($role === 'admin') {
            return Report::list($this->pdo, $status, null);
        }

        // Teacher (and any other role): only what they created
        return Report::list($this->pdo, $status, $userId);
    }

    public function createForRole(string $role, string $userId): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['ok' => false, 'error' => 'Invalid request'];
        }

        if (!$this->validateCsrfFromPost()) {
            return ['ok' => false, 'error' => 'Invalid CSRF token'];
        }

        if ($role !== 'teacher' && $role !== 'admin') {
            return ['ok' => false, 'error' => 'Forbidden'];
        }

        $data = $this->validateAndNormalizePost($userId);
        if (!$data['ok']) {
            return $data;
        }

        $ok = Report::create($this->pdo, $data['data']);
        return ['ok' => $ok, 'error' => $ok ? null : 'Failed to create report'];
    }

    public function updateForRole(string $role, string $userId, int $id): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['ok' => false, 'error' => 'Invalid request'];
        }

        if (!$this->validateCsrfFromPost()) {
            return ['ok' => false, 'error' => 'Invalid CSRF token'];
        }

        $existing = Report::getById($this->pdo, $id);
        if (!$existing) {
            return ['ok' => false, 'error' => 'Report not found'];
        }

        if ($role !== 'admin' && ($existing['created_by'] ?? '') !== $userId) {
            return ['ok' => false, 'error' => 'Forbidden'];
        }

        $data = $this->validateAndNormalizePost($existing['created_by'] ?? $userId);
        if (!$data['ok']) {
            return $data;
        }

        $ok = Report::update($this->pdo, $id, $data['data']);
        return ['ok' => $ok, 'error' => $ok ? null : 'Failed to update report'];
    }

    public function updateStatusForRole(string $role, string $userId, int $id, string $status): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['ok' => false, 'error' => 'Invalid request'];
        }

        if (!$this->validateCsrfFromPost()) {
            return ['ok' => false, 'error' => 'Invalid CSRF token'];
        }

        if (!in_array($status, self::allowedStatuses(), true)) {
            return ['ok' => false, 'error' => 'Invalid status'];
        }

        $existing = Report::getById($this->pdo, $id);
        if (!$existing) {
            return ['ok' => false, 'error' => 'Report not found'];
        }

        if ($role !== 'admin' && ($existing['created_by'] ?? '') !== $userId) {
            return ['ok' => false, 'error' => 'Forbidden'];
        }

        $ok = Report::updateStatus($this->pdo, $id, $status);
        return ['ok' => $ok, 'error' => $ok ? null : 'Failed to update status'];
    }

    public function deleteForRole(string $role, string $userId, int $id): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['ok' => false, 'error' => 'Invalid request'];
        }

        if (!$this->validateCsrfFromPost()) {
            return ['ok' => false, 'error' => 'Invalid CSRF token'];
        }

        $existing = Report::getById($this->pdo, $id);
        if (!$existing) {
            return ['ok' => false, 'error' => 'Report not found'];
        }

        if ($role !== 'admin' && ($existing['created_by'] ?? '') !== $userId) {
            return ['ok' => false, 'error' => 'Forbidden'];
        }

        $ok = Report::delete($this->pdo, $id);
        return ['ok' => $ok, 'error' => $ok ? null : 'Failed to delete report'];
    }

    private function validateCsrfFromPost(): bool
    {
        $posted = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (!is_string($posted) || $posted === '' || !is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $posted);
    }

    private function validateAndNormalizePost(string $createdBy): array
    {
        $student = trim((string)($_POST['student'] ?? ''));
        $quiz = trim((string)($_POST['quiz'] ?? ''));
        $type = trim((string)($_POST['type'] ?? ''));
        $status = trim((string)($_POST['status'] ?? 'Pending'));
        $content = trim((string)($_POST['content'] ?? ''));

        if ($student === '' || $type === '' || $content === '') {
            return ['ok' => false, 'error' => 'Student, Type, and Content are required'];
        }

        if (!in_array($type, self::allowedTypes(), true)) {
            return ['ok' => false, 'error' => 'Invalid report type'];
        }

        if (!in_array($status, self::allowedStatuses(), true)) {
            $status = 'Pending';
        }

        return [
            'ok' => true,
            'data' => [
                'student' => $student,
                'quiz' => $quiz !== '' ? $quiz : null,
                'type' => $type,
                'status' => $status,
                'content' => $content,
                'created_by' => $createdBy,
            ],
        ];
    }
}
