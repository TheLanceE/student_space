<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Challenges.php';
require_once __DIR__ . '/../../Models/Points.php';

class ChallengesController {

    public static function getAll($pdo) {
        try {
            $data = Challenges::getAll($pdo);
            echo json_encode(['success' => true, 'challenges' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error fetching challenges']);
        }
    }

    public static function complete($pdo, $challengeID, $studentID) {
        try {
            $pointsAwarded = Challenges::complete($pdo, $challengeID, $studentID);
            if ($pointsAwarded === false) {
                echo json_encode(['success' => false, 'message' => '⚠️ Challenge already completed!']);
                return;
            }
            $newBalance = Points::getBalance($pdo, $studentID);
            echo json_encode(['success' => true, 'points' => $pointsAwarded, 'newBalance' => $newBalance]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error completing challenge']);
        }
    }

    public static function create($pdo) {
        try {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $type = $_POST['type'] ?? 'quiz';
            $points = (int)($_POST['points'] ?? 0);
            $criteria = $_POST['criteria'] ?? '';
            $status = $_POST['status'] ?? 'active';
            $createdBy = $_SESSION['userID'] ?? 1;
            $ok = Challenges::create($pdo, $title, $description, $type, $points, $criteria, $status, $createdBy);
            echo json_encode(['success' => $ok, 'message' => $ok ? '🎉 Challenge created!' : '⚠️ Creation failed']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error creating challenge']);
        }
    }

    public static function update($pdo, $id) {
        try {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $type = $_POST['type'] ?? 'quiz';
            $points = (int)($_POST['points'] ?? 0);
            $criteria = $_POST['criteria'] ?? '';
            $status = $_POST['status'] ?? 'active';
            $ok = Challenges::update($pdo, $id, $title, $description, $type, $points, $criteria, $status);
            echo json_encode(['success' => $ok, 'message' => $ok ? '✨ Challenge updated!' : '⚠️ Update failed']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error updating challenge']);
        }
    }

    public static function delete($pdo, $id) {
        try {
            $ok = Challenges::delete($pdo, $id);
            echo json_encode(['success' => $ok, 'message' => $ok ? '🗑️ Challenge deleted!' : '⚠️ Deletion failed']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error deleting challenge']);
        }
    }

    public static function leaderboard($pdo) {
        try {
            $data = Challenges::leaderboard($pdo);
            echo json_encode(['success' => true, 'leaderboard' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error fetching leaderboard']);
        }
    }

    public static function get($pdo, $id) {
    try {
        $data = Challenges::getByID($pdo, $id);
        if (!$data) {
            echo json_encode(['success' => false, 'message' => '⚠️ Challenge not found']);
            return;
        }
        echo json_encode(['success' => true, 'challenge' => $data]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => '⚠️ Error fetching challenge',
            'error' => $e->getMessage() // shows the real error
        ]);
    }
}


}

$action = $_GET['action'] ?? null;
$studentID = $_SESSION['userID'] ?? 1;
// Check if config.php has made $pdo available. If not, try to get it.
if (!isset($pdo) || is_null($pdo)) {
    // This is a placeholder/example. You need to verify how your config.php
    // handles the connection.
    try {
        require_once __DIR__ . '/../../config.php'; // Assuming config.php sets $pdo
    } catch (\Throwable $th) {
        // Fallback for if $pdo is still not set after requiring config
        // You might need to call a function or instantiate a class from config.php
        // to get the connection, depending on your setup.
    }
}

// Keep the logic for studentID and action
$action = $_GET['action'] ?? null;
$studentID = $_SESSION['userID'] ?? 1;

// Now, check if $pdo is actually an object before the switch
if (!is_object($pdo) || get_class($pdo) !== 'PDO') {
    // If $pdo is still not set or not a valid PDO object, exit with an error.
    echo json_encode(['success' => false, 'message' => '⚠️ Database connection error: PDO is not initialized.']);
    exit;
}

switch ($action) {
    case 'all':
        ChallengesController::getAll($pdo);
        break;
    case 'complete':
        ChallengesController::complete($pdo, (int)$_GET['id'], $studentID);
        break;
    case 'create':
        ChallengesController::create($pdo);
        break;
    case 'update':
        ChallengesController::update($pdo, (int)$_GET['id']);
        break;
    case 'delete':
        ChallengesController::delete($pdo, (int)$_GET['id']);
        break;
    case 'leaderboard':
        ChallengesController::leaderboard($pdo);
        break;
    case 'get':
        ChallengesController::get($pdo, (int)$_GET['id']);
        break;
    default:
        echo json_encode(['success' => false, 'message' => '⚠️ Invalid action']);
}
?>