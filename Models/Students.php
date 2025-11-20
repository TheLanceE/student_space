<?php
class Students {

    public static function getAll($pdo) {
        $stmt = $pdo->prepare("SELECT id, name, points FROM students ORDER BY points DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByID($pdo, $id) {
        $stmt = $pdo->prepare("SELECT id, name, email, points FROM students WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>