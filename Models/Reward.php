<?php

require_once __DIR__ . '/Points.php';

final class Reward
{
    public static function listAll(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT * FROM rewards ORDER BY category ASC, costPoints ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(PDO $pdo, array $data): bool
    {
        $stmt = $pdo->prepare(
            'INSERT INTO rewards (id, name, category, costPoints, tierRequired, stock, createdAt) VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );

        return $stmt->execute([
            $data['id'],
            $data['name'],
            $data['category'],
            (int)$data['costPoints'],
            $data['tierRequired'],
            $data['stock'],
        ]);
    }

    public static function update(PDO $pdo, string $id, array $data): bool
    {
        $stmt = $pdo->prepare('UPDATE rewards SET name = ?, category = ?, costPoints = ?, tierRequired = ?, stock = ? WHERE id = ?');
        return $stmt->execute([
            $data['name'],
            $data['category'],
            (int)$data['costPoints'],
            $data['tierRequired'],
            $data['stock'],
            $id,
        ]);
    }

    public static function delete(PDO $pdo, string $id): bool
    {
        $stmt = $pdo->prepare('DELETE FROM rewards WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function getById(PDO $pdo, string $id): ?array
    {
        $stmt = $pdo->prepare('SELECT * FROM rewards WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function redeemOrRequest(PDO $pdo, array $reward, string $studentId): array
    {
        $balance = Points::getBalance($pdo, $studentId);
        $cost = (int)($reward['costPoints'] ?? 0);

        $tierRequired = $reward['tierRequired'] ?? null;
        if ($tierRequired !== null) {
            $tier = Points::tierForPoints($balance);
            if ((int)$tier['current']['id'] < (int)$tierRequired) {
                return ['success' => false, 'error' => 'Tier requirement not met'];
            }
        }

        $shortBy = $cost - $balance;
        $canInstantRedeem = ($shortBy <= 0);
        $canRequestApproval = ($shortBy > 0 && $shortBy <= 10);

        // Stock check (NULL = unlimited)
        $stock = $reward['stock'];
        if ($stock !== null && (int)$stock <= 0) {
            return ['success' => false, 'error' => 'Out of stock'];
        }

        $pdo->beginTransaction();
        try {
            $redemptionId = 'rred_' . bin2hex(random_bytes(8));

            if ($canInstantRedeem) {
                // Deduct points now
                $ok = Points::add($pdo, $studentId, -$cost, 'Reward redeemed: ' . (string)$reward['name'], 'reward', (string)$reward['id']);
                if (!$ok) {
                    $pdo->rollBack();
                    return ['success' => false, 'error' => 'Failed to deduct points'];
                }

                // Decrement stock if applicable
                if ($stock !== null) {
                    $upd = $pdo->prepare('UPDATE rewards SET stock = stock - 1 WHERE id = ? AND stock > 0');
                    $upd->execute([(string)$reward['id']]);
                    if ($upd->rowCount() < 1) {
                        $pdo->rollBack();
                        return ['success' => false, 'error' => 'Out of stock'];
                    }
                }

                $stmt = $pdo->prepare('INSERT INTO reward_redemptions (id, rewardId, studentId, status, requestedBalance, shortBy, requestedAt) VALUES (?, ?, ?, \'redeemed\', ?, 0, NOW())');
                $stmt->execute([$redemptionId, (string)$reward['id'], $studentId, $balance]);

                $pdo->commit();
                return ['success' => true, 'status' => 'redeemed'];
            }

            if ($canRequestApproval) {
                // Avoid duplicate pending requests for the same reward
                $dup = $pdo->prepare("SELECT 1 FROM reward_redemptions WHERE rewardId = ? AND studentId = ? AND status = 'pending' LIMIT 1");
                $dup->execute([(string)$reward['id'], $studentId]);
                if ((bool)$dup->fetchColumn()) {
                    $pdo->rollBack();
                    return ['success' => false, 'error' => 'You already have a pending request for this reward'];
                }

                $stmt = $pdo->prepare('INSERT INTO reward_redemptions (id, rewardId, studentId, status, requestedBalance, shortBy, requestedAt) VALUES (?, ?, ?, \'pending\', ?, ?, NOW())');
                $stmt->execute([$redemptionId, (string)$reward['id'], $studentId, $balance, $shortBy]);
                $pdo->commit();
                return ['success' => true, 'status' => 'pending'];
            }

            $pdo->rollBack();
            return ['success' => false, 'error' => 'Not enough points'];
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('[Reward] Redeem error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to redeem'];
        }
    }

    public static function listPendingRequestsForTeacher(PDO $pdo): array
    {
        // Teachers approve based on student’s courses in this app isn’t explicit; keep it global pending list.
        // Admin remains the global manager.
        $sql = "
            SELECT rr.*, r.name AS rewardName, r.costPoints, r.category,
                   s.username AS studentUsername, s.fullName AS studentName
            FROM reward_redemptions rr
            JOIN rewards r ON r.id = rr.rewardId
            JOIN students s ON s.id = rr.studentId
            WHERE rr.status = 'pending'
            ORDER BY rr.requestedAt DESC
        ";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function approveRequest(PDO $pdo, string $redemptionId, string $teacherId, ?string $note = null): array
    {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "SELECT rr.*, r.name AS rewardName, r.costPoints, r.stock FROM reward_redemptions rr JOIN rewards r ON r.id = rr.rewardId WHERE rr.id = ? FOR UPDATE"
            );
            $stmt->execute([$redemptionId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || (string)$row['status'] !== 'pending') {
                $pdo->rollBack();
                return ['success' => false, 'error' => 'Request not found'];
            }

            $studentId = (string)$row['studentId'];
            $cost = (int)$row['costPoints'];
            $shortBy = (int)($row['shortBy'] ?? 0);

            // Re-check current balance; allow approval only when current shortBy <= 10.
            $balance = Points::getBalance($pdo, $studentId);

            $tierRequiredStmt = $pdo->prepare('SELECT tierRequired FROM rewards WHERE id = ?');
            $tierRequiredStmt->execute([(string)$row['rewardId']]);
            $tierRequired = $tierRequiredStmt->fetchColumn();
            if ($tierRequired !== false && $tierRequired !== null) {
                $tier = Points::tierForPoints($balance);
                if ((int)$tier['current']['id'] < (int)$tierRequired) {
                    $pdo->rollBack();
                    return ['success' => false, 'error' => 'Tier requirement not met'];
                }
            }

            $currentShortBy = $cost - $balance;
            if ($currentShortBy > 10) {
                $pdo->rollBack();
                return ['success' => false, 'error' => 'Student is too far short of points now'];
            }

            // Stock check
            $stock = $row['stock'];
            if ($stock !== null && (int)$stock <= 0) {
                $pdo->rollBack();
                return ['success' => false, 'error' => 'Out of stock'];
            }

            // Deduct points even if it goes slightly negative
            $ok = Points::add($pdo, $studentId, -$cost, 'Reward approved: ' . (string)$row['rewardName'], 'reward', (string)$row['rewardId']);
            if (!$ok) {
                $pdo->rollBack();
                return ['success' => false, 'error' => 'Failed to deduct points'];
            }

            if ($stock !== null) {
                $upd = $pdo->prepare('UPDATE rewards SET stock = stock - 1 WHERE id = ? AND stock > 0');
                $upd->execute([(string)$row['rewardId']]);
                if ($upd->rowCount() < 1) {
                    $pdo->rollBack();
                    return ['success' => false, 'error' => 'Out of stock'];
                }
            }

            $updReq = $pdo->prepare("UPDATE reward_redemptions SET status = 'redeemed', reviewedBy = ?, reviewedAt = NOW(), note = ? WHERE id = ?");
            $updReq->execute([$teacherId, $note, $redemptionId]);

            $pdo->commit();
            return ['success' => true];
        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => 'Failed to approve'];
        }
    }

    public static function rejectRequest(PDO $pdo, string $redemptionId, string $teacherId, ?string $note = null): bool
    {
        $stmt = $pdo->prepare("UPDATE reward_redemptions SET status = 'rejected', reviewedBy = ?, reviewedAt = NOW(), note = ? WHERE id = ? AND status = 'pending'");
        return $stmt->execute([$teacherId, $note, $redemptionId]);
    }

    public static function listForStudent(PDO $pdo, string $studentId): array
    {
        $stmt = $pdo->prepare(
            "SELECT rr.*, r.name AS rewardName, r.category, r.costPoints FROM reward_redemptions rr JOIN rewards r ON r.id = rr.rewardId WHERE rr.studentId = ? ORDER BY rr.requestedAt DESC"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
