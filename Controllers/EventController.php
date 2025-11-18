<?php
require_once __DIR__ . "/../Models/Event.php";
require_once __DIR__ . "/config.php";

class EventController
{
    private $pdo;

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
        $desc = $_POST["desc"] ?? '';

        // If not a lecture, clear location
        if ($type !== "Lecture") {
            $location = "";
        }

        // Get teacher ID from session (fallback to 0 for now)
        session_start();
        $teacherID = $_SESSION['teacher_id'] ?? 0;

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

        $id = intval($_POST['deleteID']);
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
