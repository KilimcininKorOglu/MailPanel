<?php

declare(strict_types=1);

namespace App\Repositories\Mysql;

use App\Repositories\WhiteBlacklistRepositoryInterface;

class MysqlWhiteBlacklistRepository implements WhiteBlacklistRepositoryInterface
{
    public function getInboundList(string $account): array
    {
        return $this->getList($account, 'wblist');
    }

    public function addInboundEntry(string $account, string $sender, string $wb): bool
    {
        return $this->addEntry($account, $sender, $wb, 'wblist');
    }

    public function removeInboundEntry(string $account, string $sender): bool
    {
        return $this->removeEntry($account, $sender, 'wblist');
    }

    public function getOutboundList(string $account): array
    {
        return $this->getList($account, 'outbound_wblist');
    }

    public function addOutboundEntry(string $account, string $recipient, string $wb): bool
    {
        return $this->addEntry($account, $recipient, $wb, 'outbound_wblist');
    }

    public function removeOutboundEntry(string $account, string $recipient): bool
    {
        return $this->removeEntry($account, $recipient, 'outbound_wblist');
    }

    public function getOrCreateUserId(string $email): int
    {
        $pdo = AmavisdConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if ($row !== false) {
            return (int) $row['id'];
        }

        $priority = $this->getPriority($email);
        $pdo->prepare("INSERT INTO users (email, priority) VALUES (:email, :priority)")
            ->execute(['email' => $email, 'priority' => $priority]);

        return (int) $pdo->lastInsertId();
    }

    public function getOrCreateMailaddrId(string $email): int
    {
        $pdo = AmavisdConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare("SELECT id FROM mailaddr WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if ($row !== false) {
            return (int) $row['id'];
        }

        $priority = $this->getMailaddrPriority($email);
        $pdo->prepare("INSERT INTO mailaddr (email, priority) VALUES (:email, :priority)")
            ->execute(['email' => $email, 'priority' => $priority]);

        return (int) $pdo->lastInsertId();
    }

    private function getList(string $account, string $table): array
    {
        $pdo = AmavisdConnection::getInstance()->getPdo();

        $userId = $this->getUserId($account);
        if ($userId === null) {
            return [];
        }

        $stmt = $pdo->prepare(
            "SELECT m.email AS sender, w.wb
             FROM {$table} w
             JOIN mailaddr m ON w.sid = m.id
             WHERE w.rid = :rid
             ORDER BY m.email"
        );
        $stmt->execute(['rid' => $userId]);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'sender' => $row['sender'],
                'wb' => $row['wb'],
            ];
        }

        return $results;
    }

    private function addEntry(string $account, string $sender, string $wb, string $table): bool
    {
        $pdo = AmavisdConnection::getInstance()->getPdo();

        $rid = $this->getOrCreateUserId($account);
        $sid = $this->getOrCreateMailaddrId($sender);

        $stmt = $pdo->prepare("SELECT 1 FROM {$table} WHERE rid = :rid AND sid = :sid LIMIT 1");
        $stmt->execute(['rid' => $rid, 'sid' => $sid]);

        if ($stmt->fetch() !== false) {
            $pdo->prepare("UPDATE {$table} SET wb = :wb WHERE rid = :rid AND sid = :sid")
                ->execute(['wb' => $wb, 'rid' => $rid, 'sid' => $sid]);
        } else {
            $pdo->prepare("INSERT INTO {$table} (rid, sid, wb) VALUES (:rid, :sid, :wb)")
                ->execute(['rid' => $rid, 'sid' => $sid, 'wb' => $wb]);
        }

        return true;
    }

    private function removeEntry(string $account, string $sender, string $table): bool
    {
        $pdo = AmavisdConnection::getInstance()->getPdo();

        $userId = $this->getUserId($account);
        if ($userId === null) {
            return true;
        }

        $stmt = $pdo->prepare("SELECT id FROM mailaddr WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $sender]);
        $row = $stmt->fetch();

        if ($row === false) {
            return true;
        }

        $pdo->prepare("DELETE FROM {$table} WHERE rid = :rid AND sid = :sid")
            ->execute(['rid' => $userId, 'sid' => $row['id']]);

        return true;
    }

    private function getUserId(string $email): ?int
    {
        $pdo = AmavisdConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row !== false ? (int) $row['id'] : null;
    }

    private function getPriority(string $email): int
    {
        if ($email === '@.') {
            return 0;
        }
        if (str_starts_with($email, '@')) {
            return 2;
        }
        return 7;
    }

    private function getMailaddrPriority(string $email): int
    {
        if ($email === '@.') {
            return 0;
        }
        if (str_starts_with($email, '@')) {
            return 2;
        }
        return 7;
    }
}
