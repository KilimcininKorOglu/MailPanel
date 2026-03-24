<?php

declare(strict_types=1);

namespace App\Repositories\Pgsql;

use App\Repositories\DomainOwnershipRepositoryInterface;

class PgsqlDomainOwnershipRepository implements DomainOwnershipRepositoryInterface
{
    public function getPendingDomains(): array
    {
        $pdo = IredadminPgsqlConnection::getInstance()->getPdo();

        try {
            $stmt = $pdo->query(
                "SELECT admin, domain, verify_code, verified, expire
                 FROM domain_ownership ORDER BY domain"
            );
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function addPendingDomain(string $admin, string $domain, string $verifyCode, int $expireTimestamp): bool
    {
        $pdo = IredadminPgsqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare(
            "INSERT INTO domain_ownership (admin, domain, verify_code, verified, expire)
             VALUES (:admin, :domain, :code, 0, :expire)"
        );
        return $stmt->execute([
            'admin' => $admin,
            'domain' => $domain,
            'code' => $verifyCode,
            'expire' => $expireTimestamp,
        ]);
    }

    public function getVerifyCode(string $domain): ?string
    {
        $pdo = IredadminPgsqlConnection::getInstance()->getPdo();

        try {
            $stmt = $pdo->prepare("SELECT verify_code FROM domain_ownership WHERE domain = :domain LIMIT 1");
            $stmt->execute(['domain' => $domain]);
            $row = $stmt->fetch();
            return $row !== false ? $row['verify_code'] : null;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function markVerified(string $domain): bool
    {
        $pdo = IredadminPgsqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare("UPDATE domain_ownership SET verified = 1 WHERE domain = :domain");
        return $stmt->execute(['domain' => $domain]);
    }

    public function isVerified(string $domain): bool
    {
        $pdo = IredadminPgsqlConnection::getInstance()->getPdo();

        try {
            $stmt = $pdo->prepare("SELECT verified FROM domain_ownership WHERE domain = :domain LIMIT 1");
            $stmt->execute(['domain' => $domain]);
            $row = $stmt->fetch();
            return $row !== false && (bool) $row['verified'];
        } catch (\PDOException $e) {
            return true;
        }
    }

    public function deletePendingDomain(string $domain): bool
    {
        $pdo = IredadminPgsqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare("DELETE FROM domain_ownership WHERE domain = :domain");
        return $stmt->execute(['domain' => $domain]);
    }

    public function verifyDnsTxt(string $domain, string $verifyCode): bool
    {
        $records = @dns_get_record($domain, DNS_TXT);
        if ($records === false || empty($records)) {
            return false;
        }

        foreach ($records as $record) {
            $txt = $record['txt'] ?? '';
            if (str_contains($txt, $verifyCode)) {
                return true;
            }
        }

        return false;
    }
}
