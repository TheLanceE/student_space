<?php
class Challenges {

    public static function getAll($pdo) {
        $stmt = $pdo->prepare("SELECT id, title, description, type, points, criteria, status FROM challenges ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function isCompleted($pdo, $studentID, $challengeID) {
        $stmt = $pdo->prepare("SELECT id FROM challenge_completions WHERE studentID = ? AND challengeID = ?");
        $stmt->execute([$studentID, $challengeID]);
        return $stmt->rowCount() > 0;
    }

    public static function complete($pdo, $challengeID, $studentID) {
        if (self::isCompleted($pdo, $studentID, $challengeID)) {
            return false;
        }
        $stmt = $pdo->prepare("SELECT points FROM challenges WHERE id = ?");
        $stmt->execute([$challengeID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;
        $pointsAwarded = $row['points'];
        $pdo->beginTransaction();
        try {
            $pdo->prepare("INSERT INTO challenge_completions (studentID, challengeID, pointsAwarded) VALUES (?, ?, ?)")->execute([$studentID, $challengeID, $pointsAwarded]);
            Points::addPoints($pdo, $studentID, $pointsAwarded);
            $pdo->commit();
            return $pointsAwarded;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public static function create($pdo, $title, $description, $type, $points, $criteria, $status, $createdBy) {
        $stmt = $pdo->prepare("INSERT INTO challenges (title, description, type, points, criteria, status, createdBy) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$title, $description, $type, $points, $criteria, $status, $createdBy]);
    }

    public static function update($pdo, $id, $title, $description, $type, $points, $criteria, $status) {
        $stmt = $pdo->prepare("UPDATE challenges SET title = ?, description = ?, type = ?, points = ?, criteria = ?, status = ? WHERE id = ?");
        return $stmt->execute([$title, $description, $type, $points, $criteria, $status, $id]);
    }

    public static function delete($pdo, $id) {
        $stmt = $pdo->prepare("DELETE FROM challenges WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getByCreator($pdo, $teacherID) {
        $stmt = $pdo->prepare("SELECT id, title, description, type, points, criteria, status FROM challenges WHERE createdBy = ? ORDER BY id DESC");
        $stmt->execute([$teacherID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function leaderboard($pdo) {
        $stmt = $pdo->prepare("SELECT id, name, points FROM students ORDER BY points DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    
public static function getByID($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM Challenges WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: false;
    } catch (Exception $e) {
        return false;
    }
}


}
?>