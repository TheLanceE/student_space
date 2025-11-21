<?php
class Rewards {

    public static function getByID($pdo, $rewardID) {
        $stmt = $pdo->prepare("SELECT id, title, description, type, pointsCost, availability, status FROM rewards WHERE id = ?");
        $stmt->execute([$rewardID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAll($pdo) {
        $stmt = $pdo->prepare("SELECT id, title, description, type, pointsCost, availability, status FROM rewards ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function redeem($pdo, $rewardID, $studentID) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT pointsCost FROM rewards WHERE id = ?");
            $stmt->execute([$rewardID]);
            $reward = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$reward) {
                $pdo->rollBack();
                return false;
            }
            $pdo->prepare("UPDATE students SET points = points - ? WHERE id = ?")->execute([$reward['pointsCost'], $studentID]);
            $pdo->prepare("INSERT INTO reward_redemptions (studentID, rewardID) VALUES (?, ?)")->execute([$studentID, $rewardID]);
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public static function create($pdo, $title, $description, $type, $pointsCost, $availability, $status) {
        $stmt = $pdo->prepare("INSERT INTO rewards (title, description, type, pointsCost, availability, status) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$title, $description, $type, $pointsCost, $availability, $status]);
    }

    public static function update($pdo, $id, $title, $description, $type, $pointsCost, $availability, $status) {
        $stmt = $pdo->prepare("UPDATE rewards SET title = ?, description = ?, type = ?, pointsCost = ?, availability = ?, status = ? WHERE id = ?");
        return $stmt->execute([$title, $description, $type, $pointsCost, $availability, $status, $id]);
    }

    public static function delete($pdo, $id) {
        $stmt = $pdo->prepare("DELETE FROM rewards WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
