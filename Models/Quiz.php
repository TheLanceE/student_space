<?php

class Quiz
{
    public static function getById(PDO $pdo, string $quizId): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM quizzes WHERE id = ? LIMIT 1');
        $stmt->execute([$quizId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $row['questions_decoded'] = json_decode($row['questions'] ?? '[]', true);
        if (!is_array($row['questions_decoded'])) {
            $row['questions_decoded'] = [];
        }

        return $row;
    }

    public static function listForTeacher(PDO $pdo, string $teacherId): array
    {
        $stmt = $pdo->prepare('SELECT * FROM quizzes WHERE createdBy = ? ORDER BY createdAt DESC');
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(PDO $pdo, array $data): array
    {
        $stmt = $pdo->prepare(
            'INSERT INTO quizzes (id, courseId, title, durationSec, difficulty, questions, createdBy, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
        );

        $ok = $stmt->execute([
            $data['id'],
            $data['courseId'],
            $data['title'],
            $data['durationSec'],
            $data['difficulty'],
            json_encode($data['questions'], JSON_UNESCAPED_UNICODE),
            $data['createdBy'],
        ]);

        return ['success' => $ok, 'id' => $data['id']];
    }

    public static function listAll(PDO $pdo): array
    {
        $sql = "
            SELECT q.*, c.title AS courseTitle, t.fullName AS teacherName
            FROM quizzes q
            JOIN courses c ON c.id = q.courseId
            LEFT JOIN teachers t ON t.id = q.createdBy
            ORDER BY q.createdAt DESC
        ";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function delete(PDO $pdo, string $quizId): bool
    {
        // quiz_reports doesn't have FK; clean up explicitly.
        $pdo->beginTransaction();
        try {
            $delReports = $pdo->prepare('DELETE FROM quiz_reports WHERE quizId = ?');
            $delReports->execute([$quizId]);

            $del = $pdo->prepare('DELETE FROM quizzes WHERE id = ?');
            $ok = $del->execute([$quizId]);
            $pdo->commit();
            return $ok;
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('[Quiz] Delete error: ' . $e->getMessage());
            return false;
        }
    }
}
