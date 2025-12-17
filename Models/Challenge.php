<?php

final class Challenge
{
    public static function listAll(PDO $pdo): array
    {
        $sql = "
            SELECT c.*, t.fullName AS teacherName,
                   (SELECT COUNT(*) FROM challenge_completions cc WHERE cc.challengeId = c.id) AS completions,
                   (SELECT ROUND(AVG(cc.rating), 1) FROM challenge_completions cc WHERE cc.challengeId = c.id AND cc.rating IS NOT NULL) AS avgRating
            FROM challenges c
            LEFT JOIN teachers t ON t.id = c.createdBy
            ORDER BY c.level ASC, c.createdAt DESC
        ";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function listByTeacher(PDO $pdo, string $teacherId): array
    {
        $stmt = $pdo->prepare('SELECT * FROM challenges WHERE createdBy = ? ORDER BY level ASC, createdAt DESC');
        $stmt->execute([$teacherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(PDO $pdo, array $data): bool
    {
        $stmt = $pdo->prepare(
            'INSERT INTO challenges (id, title, description, level, points, category, skillTags, prerequisiteLevel, createdBy, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
        );

        return $stmt->execute([
            $data['id'],
            $data['title'],
            $data['description'] ?? null,
            (int)($data['level'] ?? 0),
            (int)($data['points'] ?? 10),
            $data['category'] ?? null,
            $data['skillTags'] ?? null,
            $data['prerequisiteLevel'] ?? null,
            $data['createdBy'],
        ]);
    }

    public static function update(PDO $pdo, string $id, array $data, ?string $teacherId = null): bool
    {
        $where = 'id = ?';
        $params = [];
        if ($teacherId !== null) {
            $where .= ' AND createdBy = ?';
            $params[] = $teacherId;
        }

        $sql = "UPDATE challenges SET title = ?, description = ?, level = ?, points = ?, category = ?, skillTags = ?, prerequisiteLevel = ? WHERE $where";
        $stmt = $pdo->prepare($sql);
        $params = array_merge([
            $data['title'],
            $data['description'] ?? null,
            (int)($data['level'] ?? 0),
            (int)($data['points'] ?? 10),
            $data['category'] ?? null,
            $data['skillTags'] ?? null,
            $data['prerequisiteLevel'] ?? null,
        ], [$id], $params);

        return $stmt->execute($params);
    }

    public static function delete(PDO $pdo, string $id, ?string $teacherId = null): bool
    {
        $sql = 'DELETE FROM challenges WHERE id = ?';
        $params = [$id];
        if ($teacherId !== null) {
            $sql .= ' AND createdBy = ?';
            $params[] = $teacherId;
        }
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function getStudentProgress(PDO $pdo, string $studentId): array
    {
        $stmt = $pdo->prepare(
            'SELECT c.level, COUNT(*) AS cnt FROM challenge_completions cc JOIN challenges c ON c.id = cc.challengeId WHERE cc.studentId = ? GROUP BY c.level'
        );
        $stmt->execute([$studentId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $byLevel = [];
        foreach ($rows as $r) {
            $byLevel[(int)$r['level']] = (int)$r['cnt'];
        }

        return $byLevel;
    }

    public static function hasCompletedLevel(PDO $pdo, string $studentId, int $level): bool
    {
        $stmt = $pdo->prepare(
            'SELECT 1 FROM challenge_completions cc JOIN challenges c ON c.id = cc.challengeId WHERE cc.studentId = ? AND c.level = ? LIMIT 1'
        );
        $stmt->execute([$studentId, $level]);
        return (bool)$stmt->fetchColumn();
    }

    public static function complete(PDO $pdo, string $challengeId, string $studentId): bool
    {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO challenge_completions (id, challengeId, studentId, completedAt) VALUES (?, ?, ?, NOW())');
            $ok = $stmt->execute(['cc_' . bin2hex(random_bytes(8)), $challengeId, $studentId]);
            if (!$ok) {
                $pdo->rollBack();
                return false;
            }
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('[Challenge] Complete error: ' . $e->getMessage());
            return false;
        }
    }

    public static function rate(PDO $pdo, string $challengeId, string $studentId, int $rating): bool
    {
        $rating = max(1, min(5, $rating));
        $stmt = $pdo->prepare('UPDATE challenge_completions SET rating = ? WHERE challengeId = ? AND studentId = ?');
        return $stmt->execute([$rating, $challengeId, $studentId]);
    }

    public static function getById(PDO $pdo, string $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM challenges WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
