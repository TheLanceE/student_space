<?php

class Report
{
    public static function list(PDO $pdo, ?string $status = null, ?string $createdBy = null): array
    {
        $where = [];
        $params = [];

        if ($status !== null && $status !== '' && strtolower($status) !== 'all') {
            $where[] = 'status = ?';
            $params[] = $status;
        }

        if ($createdBy !== null && $createdBy !== '') {
            $where[] = 'created_by = ?';
            $params[] = $createdBy;
        }

        $sql = 'SELECT * FROM reports';
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_date DESC, id DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM reports WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(PDO $pdo, array $data): bool
    {
        $stmt = $pdo->prepare(
            'INSERT INTO reports (student, quiz, type, status, content, created_by, created_date) VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );

        return $stmt->execute([
            $data['student'],
            $data['quiz'],
            $data['type'],
            $data['status'],
            $data['content'],
            $data['created_by'],
        ]);
    }

    public static function update(PDO $pdo, int $id, array $data): bool
    {
        $stmt = $pdo->prepare(
            'UPDATE reports SET student = ?, quiz = ?, type = ?, status = ?, content = ?, updated_date = NOW() WHERE id = ?'
        );

        return $stmt->execute([
            $data['student'],
            $data['quiz'],
            $data['type'],
            $data['status'],
            $data['content'],
            $id,
        ]);
    }

    public static function updateStatus(PDO $pdo, int $id, string $status): bool
    {
        $stmt = $pdo->prepare('UPDATE reports SET status = ?, updated_date = NOW() WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    public static function delete(PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare('DELETE FROM reports WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
