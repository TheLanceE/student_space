<?php
class Users {
    public static function getByID($pdo, $id) {
        $stmt = $pdo->prepare("SELECT id, name, email, points, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }
    
    public static function getAll($pdo, $role = null) {
        if ($role) {
            $stmt = $pdo->prepare("SELECT id, name, email, points, role, created_at FROM users WHERE role = ? ORDER BY points DESC");
            $stmt->execute([$role]);
        } else {
            $stmt = $pdo->prepare("SELECT id, name, email, points, role, created_at FROM users ORDER BY role, points DESC");
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getStudents($pdo) {
        return self::getAll($pdo, 'student');
    }
    
    public static function getTeachers($pdo) {
        return self::getAll($pdo, 'teacher');
    }
    
    public static function getAdmins($pdo) {
        return self::getAll($pdo, 'admin');
    }
}
?>