<?php

declare(strict_types=1);

namespace App\Repositories\Mysql;

use App\Repositories\RelayRepositoryInterface;

class MysqlRelayRepository implements RelayRepositoryInterface
{
    public function getRelayhost(string $account): ?string
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare("SELECT relayhost FROM sender_relayhost WHERE account = :account LIMIT 1");
        $stmt->execute(['account' => $account]);

        $row = $stmt->fetch();
        return $row !== false ? $row['relayhost'] : null;
    }

    public function setRelayhost(string $account, ?string $relayhost): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        if ($relayhost === null || $relayhost === '') {
            $pdo->prepare("DELETE FROM sender_relayhost WHERE account = :account")
                ->execute(['account' => $account]);
        } else {
            $stmt = $pdo->prepare("SELECT 1 FROM sender_relayhost WHERE account = :account LIMIT 1");
            $stmt->execute(['account' => $account]);

            if ($stmt->fetch() !== false) {
                $pdo->prepare("UPDATE sender_relayhost SET relayhost = :relayhost WHERE account = :account")
                    ->execute(['relayhost' => $relayhost, 'account' => $account]);
            } else {
                $pdo->prepare("INSERT INTO sender_relayhost (account, relayhost) VALUES (:account, :relayhost)")
                    ->execute(['account' => $account, 'relayhost' => $relayhost]);
            }
        }

        return true;
    }

    public function getAllRelayhosts(?string $domain = null): array
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        if ($domain !== null) {
            $stmt = $pdo->prepare(
                "SELECT account, relayhost FROM sender_relayhost
                 WHERE account = :domain OR account LIKE :pattern
                 ORDER BY account"
            );
            $stmt->execute(['domain' => '@' . $domain, 'pattern' => '%@' . $domain]);
        } else {
            $stmt = $pdo->query("SELECT account, relayhost FROM sender_relayhost ORDER BY account");
        }

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = [
                'account' => $row['account'],
                'relayhost' => $row['relayhost'],
            ];
        }

        return $results;
    }

    public function deleteRelayhost(string $account): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $pdo->prepare("DELETE FROM sender_relayhost WHERE account = :account")
            ->execute(['account' => $account]);

        return true;
    }
}
