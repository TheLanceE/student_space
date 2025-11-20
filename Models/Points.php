<?php
class Points {

    public static function getBalance($pdo, $studentID) {
        $stmt = $pdo->prepare("SELECT points FROM students WHERE id = ?");
        $stmt->execute([$studentID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['points'] : 0;
    }

    public static function getHistory($pdo, $studentID) {
        $stmt = $pdo->prepare("SELECT action, points, created_at FROM points_history WHERE studentID = ? ORDER BY created_at DESC");
        $stmt->execute([$studentID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addPoints($pdo, $studentID, $points) {
        $pdo->prepare("UPDATE students SET points = points + ? WHERE id = ?")->execute([$points, $studentID]);
        $pdo->prepare("INSERT INTO points_history (studentID, points, action) VALUES (?, ?, 'award')")->execute([$studentID, $points]);
        return true;
    }
}
?>