<?php

declare(strict_types=1);

namespace App\Repositories\Mysql;

use App\Repositories\BccRepositoryInterface;

class MysqlBccRepository implements BccRepositoryInterface
{
    public function getDomainSenderBcc(string $domain): ?string
    {
        return $this->getBccValue('sender_bcc_domain', 'domain', $domain);
    }

    public function setDomainSenderBcc(string $domain, ?string $bccAddress): bool
    {
        return $this->setBccValue('sender_bcc_domain', 'domain', $domain, $bccAddress);
    }

    public function getDomainRecipientBcc(string $domain): ?string
    {
        return $this->getBccValue('recipient_bcc_domain', 'domain', $domain);
    }

    public function setDomainRecipientBcc(string $domain, ?string $bccAddress): bool
    {
        return $this->setBccValue('recipient_bcc_domain', 'domain', $domain, $bccAddress);
    }

    public function getUserSenderBcc(string $email): ?string
    {
        return $this->getBccValue('sender_bcc_user', 'username', $email);
    }

    public function setUserSenderBcc(string $email, ?string $bccAddress): bool
    {
        $domain = explode('@', $email, 2)[1] ?? '';
        return $this->setUserBccValue('sender_bcc_user', $email, $domain, $bccAddress);
    }

    public function getUserRecipientBcc(string $email): ?string
    {
        return $this->getBccValue('recipient_bcc_user', 'username', $email);
    }

    public function setUserRecipientBcc(string $email, ?string $bccAddress): bool
    {
        $domain = explode('@', $email, 2)[1] ?? '';
        return $this->setUserBccValue('recipient_bcc_user', $email, $domain, $bccAddress);
    }

    public function getAllDomainBcc(?string $domain = null): array
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $results = [];

        foreach (['sender_bcc_domain', 'recipient_bcc_domain'] as $table) {
            $where = $domain !== null ? "WHERE domain = :domain" : "";
            $params = $domain !== null ? ['domain' => $domain] : [];
            $type = str_contains($table, 'sender') ? 'sender' : 'recipient';

            $stmt = $pdo->prepare("SELECT domain, bcc_address, active FROM {$table} {$where} ORDER BY domain");
            $stmt->execute($params);

            while ($row = $stmt->fetch()) {
                $results[] = [
                    'domain' => $row['domain'],
                    'bcc_address' => $row['bcc_address'],
                    'type' => $type,
                    'active' => (bool) $row['active'],
                ];
            }
        }

        return $results;
    }

    public function getAllUserBcc(?string $domain = null): array
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $results = [];

        foreach (['sender_bcc_user', 'recipient_bcc_user'] as $table) {
            $where = $domain !== null ? "WHERE domain = :domain" : "";
            $params = $domain !== null ? ['domain' => $domain] : [];
            $type = str_contains($table, 'sender') ? 'sender' : 'recipient';

            $stmt = $pdo->prepare("SELECT username, bcc_address, active FROM {$table} {$where} ORDER BY username");
            $stmt->execute($params);

            while ($row = $stmt->fetch()) {
                $results[] = [
                    'username' => $row['username'],
                    'bcc_address' => $row['bcc_address'],
                    'type' => $type,
                    'active' => (bool) $row['active'],
                ];
            }
        }

        return $results;
    }

    private function getBccValue(string $table, string $keyColumn, string $keyValue): ?string
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare("SELECT bcc_address FROM {$table} WHERE {$keyColumn} = :key AND active = 1 LIMIT 1");
        $stmt->execute(['key' => $keyValue]);

        $row = $stmt->fetch();
        return $row !== false ? $row['bcc_address'] : null;
    }

    private function setBccValue(string $table, string $keyColumn, string $keyValue, ?string $bccAddress): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        if ($bccAddress === null || $bccAddress === '') {
            $pdo->prepare("DELETE FROM {$table} WHERE {$keyColumn} = :key")->execute(['key' => $keyValue]);
        } else {
            $stmt = $pdo->prepare("SELECT 1 FROM {$table} WHERE {$keyColumn} = :key LIMIT 1");
            $stmt->execute(['key' => $keyValue]);

            if ($stmt->fetch() !== false) {
                $pdo->prepare("UPDATE {$table} SET bcc_address = :bcc, modified = NOW(), active = 1 WHERE {$keyColumn} = :key")
                    ->execute(['bcc' => $bccAddress, 'key' => $keyValue]);
            } else {
                $pdo->prepare("INSERT INTO {$table} ({$keyColumn}, bcc_address, active, created) VALUES (:key, :bcc, 1, NOW())")
                    ->execute(['key' => $keyValue, 'bcc' => $bccAddress]);
            }
        }

        return true;
    }

    private function setUserBccValue(string $table, string $email, string $domain, ?string $bccAddress): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        if ($bccAddress === null || $bccAddress === '') {
            $pdo->prepare("DELETE FROM {$table} WHERE username = :email")->execute(['email' => $email]);
        } else {
            $stmt = $pdo->prepare("SELECT 1 FROM {$table} WHERE username = :email LIMIT 1");
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch() !== false) {
                $pdo->prepare("UPDATE {$table} SET bcc_address = :bcc, modified = NOW(), active = 1 WHERE username = :email")
                    ->execute(['bcc' => $bccAddress, 'email' => $email]);
            } else {
                $pdo->prepare("INSERT INTO {$table} (username, bcc_address, domain, active, created) VALUES (:email, :bcc, :domain, 1, NOW())")
                    ->execute(['email' => $email, 'bcc' => $bccAddress, 'domain' => $domain]);
            }
        }

        return true;
    }
}
