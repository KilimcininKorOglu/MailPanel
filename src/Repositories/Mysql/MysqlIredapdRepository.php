<?php

declare(strict_types=1);

namespace App\Repositories\Mysql;

use App\Repositories\IredapdRepositoryInterface;

class MysqlIredapdRepository implements IredapdRepositoryInterface
{
    public function getThrottleSettings(string $account): array
    {
        $conn = IredapdConnection::getInstance();
        if (!$conn->isAvailable()) {
            return [];
        }

        $pdo = $conn->getPdo();

        $stmt = $pdo->prepare(
            "SELECT id, account, kind, priority, period, max_msgs, max_quota, msg_size
             FROM throttle
             WHERE account = :account
             ORDER BY priority DESC"
        );
        $stmt->execute(['account' => $account]);

        return $stmt->fetchAll();
    }

    public function setThrottleSettings(string $account, string $kind, int $period, int $maxMsgs, int $maxQuota, int $msgSize): void
    {
        $conn = IredapdConnection::getInstance();
        if (!$conn->isAvailable()) {
            throw new \RuntimeException('iRedAPD database not available');
        }

        $pdo = $conn->getPdo();

        // Check if entry exists
        $stmt = $pdo->prepare(
            "SELECT id FROM throttle WHERE account = :account AND kind = :kind LIMIT 1"
        );
        $stmt->execute(['account' => $account, 'kind' => $kind]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare(
                "UPDATE throttle SET period = :period, max_msgs = :maxMsgs, max_quota = :maxQuota, msg_size = :msgSize
                 WHERE account = :account AND kind = :kind"
            );
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO throttle (account, kind, priority, period, max_msgs, max_quota, msg_size)
                 VALUES (:account, :kind, 10, :period, :maxMsgs, :maxQuota, :msgSize)"
            );
        }

        $stmt->execute([
            'account' => $account,
            'kind' => $kind,
            'period' => $period,
            'maxMsgs' => $maxMsgs,
            'maxQuota' => $maxQuota,
            'msgSize' => $msgSize,
        ]);
    }

    public function getGreylistSettings(string $account): array
    {
        $conn = IredapdConnection::getInstance();
        if (!$conn->isAvailable()) {
            return [];
        }

        $pdo = $conn->getPdo();

        $stmt = $pdo->prepare(
            "SELECT id, account, sender, comment, active
             FROM greylisting
             WHERE account = :account"
        );
        $stmt->execute(['account' => $account]);

        return $stmt->fetchAll();
    }

    public function setGreylistEnabled(string $account, bool $enabled): void
    {
        $conn = IredapdConnection::getInstance();
        if (!$conn->isAvailable()) {
            throw new \RuntimeException('iRedAPD database not available');
        }

        $pdo = $conn->getPdo();

        $stmt = $pdo->prepare(
            "SELECT id FROM greylisting WHERE account = :account AND sender = '@.' LIMIT 1"
        );
        $stmt->execute(['account' => $account]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare(
                "UPDATE greylisting SET active = :active WHERE account = :account AND sender = '@.'"
            );
            $stmt->execute(['active' => $enabled ? 1 : 0, 'account' => $account]);
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO greylisting (account, sender, active, comment)
                 VALUES (:account, '@.', :active, '')"
            );
            $stmt->execute(['account' => $account, 'active' => $enabled ? 1 : 0]);
        }
    }

    /**
     * @return string[]
     */
    public function getWhitelistedSenders(string $account): array
    {
        $conn = IredapdConnection::getInstance();
        if (!$conn->isAvailable()) {
            return [];
        }

        $pdo = $conn->getPdo();

        $stmt = $pdo->prepare(
            "SELECT sender FROM greylisting_whitelists
             WHERE account = :account
             ORDER BY sender"
        );
        $stmt->execute(['account' => $account]);

        $senders = [];
        while ($row = $stmt->fetch()) {
            $senders[] = $row['sender'];
        }

        return $senders;
    }

    public function setWhitelistedSenders(string $account, array $senders): void
    {
        $conn = IredapdConnection::getInstance();
        if (!$conn->isAvailable()) {
            throw new \RuntimeException('iRedAPD database not available');
        }

        $pdo = $conn->getPdo();

        // Remove existing whitelist entries
        $stmt = $pdo->prepare("DELETE FROM greylisting_whitelists WHERE account = :account");
        $stmt->execute(['account' => $account]);

        // Insert new entries
        $stmt = $pdo->prepare(
            "INSERT INTO greylisting_whitelists (account, sender, comment)
             VALUES (:account, :sender, '')"
        );
        foreach ($senders as $sender) {
            $sender = trim($sender);
            if ($sender !== '') {
                $stmt->execute(['account' => $account, 'sender' => $sender]);
            }
        }
    }

    public function getGreylistTrackingPaginated(int $page, int $perPage): \App\Models\PaginatedResult
    {
        $conn = IredapdConnection::getInstance();
        if (!$conn->isAvailable()) {
            return new \App\Models\PaginatedResult([], 0, $page, $perPage);
        }

        $pdo = $conn->getPdo();

        try {
            $countStmt = $pdo->query("SELECT COUNT(*) AS total FROM greylisting_tracking WHERE passed = 1");
            $totalCount = (int) $countStmt->fetch()['total'];

            $offset = ($page - 1) * $perPage;

            $stmt = $pdo->prepare(
                "SELECT sender, recipient, client_address, init_time, record_expired, passed, blocked_count
                 FROM greylisting_tracking
                 WHERE passed = 1
                 ORDER BY init_time DESC
                 LIMIT :perPage OFFSET :offset"
            );
            $stmt->bindValue('perPage', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();

            $items = [];
            while ($row = $stmt->fetch()) {
                $items[] = $row;
            }

            return new \App\Models\PaginatedResult($items, $totalCount, $page, $perPage);
        } catch (\PDOException $e) {
            return new \App\Models\PaginatedResult([], 0, $page, $perPage);
        }
    }
}
