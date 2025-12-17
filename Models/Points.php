<?php

final class Points
{
    public static function getBalance(PDO $pdo, string $studentId): int
    {
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(delta), 0) AS bal FROM points_ledger WHERE studentId = ?');
        $stmt->execute([$studentId]);
        return (int)$stmt->fetchColumn();
    }

    public static function add(PDO $pdo, string $studentId, int $delta, string $reason, ?string $refType = null, ?string $refId = null): bool
    {
        $stmt = $pdo->prepare(
            'INSERT INTO points_ledger (id, studentId, delta, reason, refType, refId, createdAt) VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );

        return $stmt->execute([
            'pt_' . bin2hex(random_bytes(8)),
            $studentId,
            $delta,
            $reason,
            $refType,
            $refId,
        ]);
    }

    public static function tierForPoints(int $points): array
    {
        // Simple tiering (matches “tier progression” concept without extra tables).
        $tiers = [
            ['id' => 1, 'name' => 'Bronze', 'min' => 0],
            ['id' => 2, 'name' => 'Silver', 'min' => 100],
            ['id' => 3, 'name' => 'Gold', 'min' => 250],
            ['id' => 4, 'name' => 'Platinum', 'min' => 500],
        ];

        $current = $tiers[0];
        foreach ($tiers as $t) {
            if ($points >= $t['min']) {
                $current = $t;
            }
        }

        $next = null;
        foreach ($tiers as $t) {
            if ($t['min'] > $current['min']) {
                $next = $t;
                break;
            }
        }

        return ['current' => $current, 'next' => $next];
    }
}
