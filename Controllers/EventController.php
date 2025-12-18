<?php
/**
 * EventController - Handles calendar event CRUD operations
 * Creates, updates, deletes and lists events for teachers/admins
 */
require_once __DIR__ . "/../Models/Event.php";
require_once __DIR__ . "/config.php";

/**
 * Controller for event management
 */
class EventController
{
    /** @var PDO Database connection */
    private $pdo;

    /**
     * Validate CSRF token from POST request
     * @return bool True if token is valid
     */
    private function validateCsrfFromPost(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            // config.php should have started the session, but guard anyway
            @session_start();
        }

        $posted = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (!is_string($posted) || $posted === '' || !is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $posted);
    }

    public function __construct($pdo = null)
    {
        if ($pdo === null) {
            global $db_connection;
            $this->pdo = $db_connection;
        } else {
            $this->pdo = $pdo;
        }
    }

    public function create()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return;
        }

        if (!$this->validateCsrfFromPost()) {
            return false;
        }

        $title = $_POST["title"] ?? '';
        $date = $_POST["date"] ?? '';
        $startTime = $_POST["startTime"] ?? '';
        $endTime = $_POST["endTime"] ?? '';
        $course = $_POST["course"] ?? '';
        $type = $_POST["type"] ?? 'Other';
        $location = $_POST["location"] ?? '';
        $recurring = $_POST["recurring"] ?? 'None';
        $maxParticipants = intval($_POST["maxParticipants"] ?? 0);
        $links = $_POST["links"] ?? '';
        $desc = $_POST["desc"] ?? ($_POST['description'] ?? '');

        // If not a lecture, clear location
        if ($type !== "Lecture") {
            $location = "";
        }

        // Get teacher ID from session (string format like 'teach_jane')
        // Session already initialized by config.php
        $teacherID = (string)($_SESSION['teacher_id'] ?? $_SESSION['user_id'] ?? '');

        $event = new Event(
            $title, 
            $date,
            $startTime, 
            $endTime,
            $maxParticipants, 
            0,
            $course, 
            $type,
            $location, 
            $desc,
            $teacherID
        );

        $event->create($this->pdo);
        return true;
    }

    public function delete()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['deleteID'])) {
            return false;
        }

        if (!$this->validateCsrfFromPost()) {
            return false;
        }

        $id = $_POST['deleteID'];
        return Event::delete($this->pdo, $id);
    }

    public function getAll()
    {
        return Event::getAll($this->pdo);
    }

    public function getByTeacher($teacherID)
    {
        return Event::getByTeacher($this->pdo, $teacherID);
    }
}
?>
