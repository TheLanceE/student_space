<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Models/Rewards.php';
require_once __DIR__ . '/../../Models/Points.php';

class RewardsController {

    public static function redeem($pdo, $rewardID, $studentID) {
        try {
            $reward = Rewards::getByID($pdo, $rewardID);
            if (!$reward) {
                echo json_encode(['success' => false, 'message' => '⚠️ Reward not found!']);
                return;
            }
            $balance = Points::getBalance($pdo, $studentID);
            if ($balance < $reward['pointsCost']) {
                echo json_encode(['success' => false, 'message' => '⚠️ Not enough points!']);
                return;
            }
            $ok = Rewards::redeem($pdo, $rewardID, $studentID);
            if ($ok) {
                echo json_encode(['success' => true, 'reward' => $reward['title'], 'newBalance' => Points::getBalance($pdo, $studentID)]);
            } else {
                echo json_encode(['success' => false, 'message' => '⚠️ Redemption failed']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error redeeming reward']);
        }
    }

    public static function getAll($pdo) {
        try {
            $data = Rewards::getAll($pdo);
            echo json_encode(['success' => true, 'rewards' => $data]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error fetching rewards']);
        }
    }

    public static function create($pdo) {
        try {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $type = $_POST['type'] ?? 'badge';
            $pointsCost = (int)($_POST['pointsCost'] ?? 0);
            $availability = (int)($_POST['availability'] ?? 0);
            $status = $_POST['status'] ?? 'active';
            $ok = Rewards::create($pdo, $title, $description, $type, $pointsCost, $availability, $status);
            echo json_encode(['success' => $ok, 'message' => $ok ? '🎉 Reward created!' : '⚠️ Creation failed']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error creating reward']);
        }
    }

    public static function update($pdo, $id) {
        try {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $type = $_POST['type'] ?? 'badge';
            $pointsCost = (int)($_POST['pointsCost'] ?? 0);
            $availability = (int)($_POST['availability'] ?? 0);
            $status = $_POST['status'] ?? 'active';
            $ok = Rewards::update($pdo, $id, $title, $description, $type, $pointsCost, $availability, $status);
            echo json_encode(['success' => $ok, 'message' => $ok ? '✨ Reward updated!' : '⚠️ Update failed']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error updating reward']);
        }
    }

    public static function delete($pdo, $id) {
        try {
            $ok = Rewards::delete($pdo, $id);
            echo json_encode(['success' => $ok, 'message' => $ok ? '🗑️ Reward deleted!' : '⚠️ Deletion failed']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '⚠️ Error deleting reward']);
        }
    }
}

$action = $_GET['action'] ?? null;
$studentID = $_SESSION['userID'] ?? 1;
$pdo = $pdo ?? null;

switch ($action) {
    case 'redeem':
        RewardsController::redeem($pdo, (int)$_GET['id'], $studentID);
        break;
    case 'getAll':
        RewardsController::getAll($pdo);
        break;
    case 'create':
        RewardsController::create($pdo);
        break;
    case 'update':
        RewardsController::update($pdo, (int)$_GET['id']);
        break;
    case 'delete':
        RewardsController::delete($pdo, (int)$_GET['id']);
        break;
    default:
        echo json_encode(['success' => false, 'message' => '⚠️ Invalid action']);
}
?>