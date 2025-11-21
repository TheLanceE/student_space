<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Points.php';

class PointsController {

    public static function getBalance($pdo, $studentID) {
        try {
            $balance = Points::getBalance($pdo, $studentID);
            echo json_encode(['success' => true, 'balance' => (int)$balance]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error fetching balance']);
        }
    }

    public static function getHistory($pdo, $studentID) {
        try {
            $history = Points::getHistory($pdo, $studentID);
            echo json_encode(['success' => true, 'history' => $history]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error fetching history']);
        }
    }

    public static function award($pdo, $studentID, $points) {
        try {
            $ok = Points::addPoints($pdo, $studentID, $points);
            $newBalance = Points::getBalance($pdo, $studentID);
            echo json_encode(['success' => $ok, 'newBalance' => $newBalance, 'message' => $ok ? '🎉 Points awarded!' : '⚠️ Award failed']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error awarding points']);
        }
    }
}

$action = $_GET['action'] ?? null;
$studentID = $_SESSION['userID'] ?? 1;
$pdo = $pdo ?? null;

switch ($action) {
    case 'getBalance':
        PointsController::getBalance($pdo, $studentID);
        break;
    case 'getHistory':
        PointsController::getHistory($pdo, $studentID);
        break;
    case 'award':
        PointsController::award($pdo, $studentID, (int)$_GET['points']);
        break;
    default:
        echo json_encode(['success' => false, 'message' => '⚠️ Invalid action']);
}
?>
