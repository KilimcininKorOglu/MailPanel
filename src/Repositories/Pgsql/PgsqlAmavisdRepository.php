<?php

declare(strict_types=1);

namespace App\Repositories\Pgsql;

use App\Models\PaginatedResult;
use App\Repositories\AmavisdRepositoryInterface;

class PgsqlAmavisdRepository implements AmavisdRepositoryInterface
{
    public function getQuarantinedMessages(int $page, int $perPage, ?string $domain = null): PaginatedResult
    {
        $conn = AmavisdPgsqlConnection::getInstance();
        if (!$conn->isAvailable()) {
            return new PaginatedResult([], 0, $page, $perPage);
        }

        $pdo = $conn->getPdo();
        $offset = ($page - 1) * $perPage;

        $where = '1=1';
        $params = [];
        if ($domain !== null && $domain !== '') {
            $where .= ' AND r.email LIKE :pattern';
            $params['pattern'] = "%@{$domain}";
        }

        $countStmt = $pdo->prepare(
            "SELECT COUNT(DISTINCT m.mail_id) AS total
             FROM msgs m
             JOIN msgrcpt mr ON m.mail_id = mr.mail_id
             JOIN maddr r ON mr.rid = r.id
             JOIN quarantine q ON m.mail_id = q.mail_id
             WHERE {$where}"
        );
        $countStmt->execute($params);
        $totalCount = (int) $countStmt->fetch()['total'];

        $stmt = $pdo->prepare(
            "SELECT DISTINCT m.mail_id, m.from_addr, m.subject, m.time_iso, m.content,
                    r.email AS recipient, m.spam_level
             FROM msgs m
             JOIN msgrcpt mr ON m.mail_id = mr.mail_id
             JOIN maddr r ON mr.rid = r.id
             JOIN quarantine q ON m.mail_id = q.mail_id
             WHERE {$where}
             ORDER BY m.time_num DESC
             LIMIT :perPage OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('perPage', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll();

        return new PaginatedResult($items, $totalCount, $page, $perPage);
    }

    public function releaseMessage(string $mailId): void
    {
        $conn = AmavisdPgsqlConnection::getInstance();
        if (!$conn->isAvailable()) {
            throw new \RuntimeException('Amavisd database not available');
        }

        $pdo = $conn->getPdo();

        // Get the secret_id for amavisd-release
        $stmt = $pdo->prepare("SELECT secret_id FROM msgs WHERE mail_id = :mailId LIMIT 1");
        $stmt->execute(['mailId' => $mailId]);
        $row = $stmt->fetch();

        if ($row === false) {
            throw new \RuntimeException("Quarantined message not found: {$mailId}");
        }

        $secretId = $row['secret_id'];
        $safeSecretId = escapeshellarg($secretId);

        $proc = @proc_open(
            "amavisd-release {$safeSecretId} 2>&1",
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );

        if (!is_resource($proc)) {
            throw new \RuntimeException('Failed to execute amavisd-release command');
        }

        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($proc);

        if ($exitCode !== 0) {
            throw new \RuntimeException("amavisd-release failed (exit {$exitCode}): " . trim($output ?: ''));
        }
    }

    public function deleteQuarantinedMessage(string $mailId): void
    {
        $conn = AmavisdPgsqlConnection::getInstance();
        if (!$conn->isAvailable()) {
            throw new \RuntimeException('Amavisd database not available');
        }

        $pdo = $conn->getPdo();

        $stmt = $pdo->prepare("DELETE FROM quarantine WHERE mail_id = :mailId");
        $stmt->execute(['mailId' => $mailId]);
    }

    public function getMailLog(int $page, int $perPage, ?string $email = null): PaginatedResult
    {
        $conn = AmavisdPgsqlConnection::getInstance();
        if (!$conn->isAvailable()) {
            return new PaginatedResult([], 0, $page, $perPage);
        }

        $pdo = $conn->getPdo();
        $offset = ($page - 1) * $perPage;

        $where = '1=1';
        $params = [];
        if ($email !== null && $email !== '') {
            $where .= ' AND (m.from_addr LIKE :email OR r.email LIKE :email2)';
            $params['email'] = "%{$email}%";
            $params['email2'] = "%{$email}%";
        }

        $countStmt = $pdo->prepare(
            "SELECT COUNT(*) AS total
             FROM msgs m
             JOIN msgrcpt mr ON m.mail_id = mr.mail_id
             JOIN maddr r ON mr.rid = r.id
             WHERE {$where}"
        );
        $countStmt->execute($params);
        $totalCount = (int) $countStmt->fetch()['total'];

        $stmt = $pdo->prepare(
            "SELECT m.mail_id, m.from_addr, m.subject, m.time_iso, m.content,
                    r.email AS recipient, m.spam_level
             FROM msgs m
             JOIN msgrcpt mr ON m.mail_id = mr.mail_id
             JOIN maddr r ON mr.rid = r.id
             WHERE {$where}
             ORDER BY m.time_num DESC
             LIMIT :perPage OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('perPage', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll();

        return new PaginatedResult($items, $totalCount, $page, $perPage);
    }

    public function cleanupQuarantined(int $olderThanDays): int
    {
        $conn = AmavisdPgsqlConnection::getInstance();
        if (!$conn->isAvailable()) {
            return 0;
        }

        $pdo = $conn->getPdo();

        $stmt = $pdo->prepare(
            "DELETE FROM quarantine
             WHERE mail_id IN (
                 SELECT q.mail_id FROM quarantine q
                 JOIN msgs m ON q.mail_id = m.mail_id
                 WHERE m.time_num < EXTRACT(EPOCH FROM (NOW() - INTERVAL '1 day' * :days))
             )"
        );
        $stmt->execute(['days' => $olderThanDays]);

        return $stmt->rowCount();
    }

    public function cleanupMailLog(int $olderThanDays): int
    {
        $conn = AmavisdPgsqlConnection::getInstance();
        if (!$conn->isAvailable()) {
            return 0;
        }

        $pdo = $conn->getPdo();

        $stmt = $pdo->prepare(
            "DELETE FROM msgs WHERE time_num < EXTRACT(EPOCH FROM (NOW() - INTERVAL '1 day' * :days))"
        );
        $stmt->execute(['days' => $olderThanDays]);

        return $stmt->rowCount();
    }
}
