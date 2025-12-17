<?php

require_once __DIR__ . '/Points.php';

final class AIInsights
{
    public static function studentPrediction(PDO $pdo, string $studentId): array
    {
        $now = new DateTimeImmutable('now');
        $start = $now->modify('first day of this month')->setTime(0, 0, 0);

        $stmt = $pdo->prepare(
            'SELECT createdAt, delta FROM points_ledger WHERE studentId = ? AND createdAt >= ? ORDER BY createdAt ASC'
        );
        $stmt->execute([$studentId, $start->format('Y-m-d H:i:s')]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $earned = 0;
        $activeDays = [];
        foreach ($rows as $r) {
            $d = (int)($r['delta'] ?? 0);
            if ($d > 0) {
                $earned += $d;
            }
            $dayKey = substr((string)($r['createdAt'] ?? ''), 0, 10);
            if ($dayKey) {
                $activeDays[$dayKey] = true;
            }
        }

        $daysActive = max(1, count($activeDays));
        $dailyAvg = $earned / $daysActive;

        $daysInMonth = (int)$now->format('t');
        $dayOfMonth = (int)$now->format('j');
        $daysRemaining = max(0, $daysInMonth - $dayOfMonth);

        $projectedEarned = (int)round($earned + ($dailyAvg * $daysRemaining * 0.9));
        $currentBalance = Points::getBalance($pdo, $studentId);
        $projectedBalance = $currentBalance + max(0, ($projectedEarned - $earned));

        $tiers = Points::tierForPoints($projectedBalance);

        return [
            'monthEarned' => $earned,
            'activeDays' => count($activeDays),
            'dailyAvg' => round($dailyAvg, 1),
            'projectedMonthEarned' => $projectedEarned,
            'currentBalance' => $currentBalance,
            'projectedBalance' => $projectedBalance,
            'projectedTier' => $tiers['current']['name'],
            'confidence' => min(95, 50 + (count($activeDays) * 8)),
            'reasoning' => 'Projection is based on your active days this month and your average points earned per active day.',
        ];
    }

    public static function adminPredictions(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT id, username, fullName FROM students ORDER BY username ASC');
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($students as $s) {
            $sid = (string)$s['id'];
            $pred = self::studentPrediction($pdo, $sid);
            $out[] = [
                'id' => $sid,
                'username' => (string)($s['username'] ?? ''),
                'fullName' => (string)($s['fullName'] ?? ''),
                'currentBalance' => $pred['currentBalance'],
                'projectedBalance' => $pred['projectedBalance'],
                'projectedTier' => $pred['projectedTier'],
                'confidence' => $pred['confidence'],
            ];
        }

        return $out;
    }
}
