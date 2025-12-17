<?php

class QuizReport
{
    public static function create(PDO $pdo, array $data): bool
    {
        $stmt = $pdo->prepare(
            'INSERT INTO quiz_reports (id, quizId, questionId, reportedBy, reportType, description, status, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
        );

        return $stmt->execute([
            $data['id'],
            $data['quizId'],
            $data['questionId'],
            $data['reportedBy'],
            $data['reportType'],
            $data['description'],
            $data['status'] ?? 'pending',
        ]);
    }

    public static function listForTeacher(PDO $pdo, string $teacherId, ?string $status = null): array
    {
        $where = 'q.createdBy = ?';
        $params = [$teacherId];

        if ($status && $status !== 'all') {
            $where .= ' AND qr.status = ?';
            $params[] = $status;
        }

        $sql = "
            SELECT qr.*, q.title AS quizTitle, q.courseId
            FROM quiz_reports qr
            JOIN quizzes q ON q.id = qr.quizId
            WHERE $where
            ORDER BY qr.createdAt DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function listAll(PDO $pdo, ?string $status = null): array
    {
        $where = '1=1';
        $params = [];

        if ($status && $status !== 'all') {
            $where .= ' AND qr.status = ?';
            $params[] = $status;
        }

        $sql = "
            SELECT qr.*, q.title AS quizTitle, q.courseId, q.createdBy AS quizTeacherId,
                   c.title AS courseTitle,
                   t.fullName AS teacherName,
                   s.username AS studentUsername, s.fullName AS studentName
            FROM quiz_reports qr
            JOIN quizzes q ON q.id = qr.quizId
            JOIN courses c ON c.id = q.courseId
            LEFT JOIN teachers t ON t.id = q.createdBy
            LEFT JOIN students s ON s.id = qr.reportedBy
            WHERE $where
            ORDER BY qr.createdAt DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function updateStatusAdmin(PDO $pdo, string $id, string $status, string $reviewedBy): bool
    {
        $stmt = $pdo->prepare('UPDATE quiz_reports SET status = ?, reviewedBy = ?, reviewedAt = NOW() WHERE id = ?');
        return $stmt->execute([$status, $reviewedBy, $id]);
    }

    public static function updateStatus(PDO $pdo, string $id, string $status, ?string $reviewedBy = null): bool
    {
        // Ensure the teacher updating the status owns the quiz (q.createdBy).
        // `reviewedBy` is expected to be the teacher id.
        $sql = '
            UPDATE quiz_reports qr
            JOIN quizzes q ON q.id = qr.quizId
            SET qr.status = ?, qr.reviewedBy = ?, qr.reviewedAt = NOW()
            WHERE qr.id = ? AND q.createdBy = ?
        ';

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$status, $reviewedBy, $id, $reviewedBy]);
    }
}
