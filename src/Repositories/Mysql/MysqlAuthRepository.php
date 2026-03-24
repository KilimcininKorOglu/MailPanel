<?php

declare(strict_types=1);

namespace App\Repositories\Mysql;

use App\Repositories\AuthRepositoryInterface;

class MysqlAuthRepository implements AuthRepositoryInterface
{
    public function authenticate(string $email, string $password): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare('SELECT password FROM admin WHERE username = :username AND active = 1');
        $stmt->execute(['username' => $email]);
        $row = $stmt->fetch();

        if ($row === false) {
            throw new \Exception("Admin user {$email} not found or inactive");
        }

        if (!self::verifyPassword($password, $row['password'])) {
            throw new \Exception("Invalid password for {$email}");
        }

        $stmt = $pdo->prepare(
            "SELECT 1 FROM domain_admins WHERE username = :username AND domain = 'ALL' LIMIT 1"
        );
        $stmt->execute(['username' => $email]);

        if ($stmt->fetch() === false) {
            throw new \Exception("User {$email} is not a global administrator");
        }

        return true;
    }

    /**
     * Verifies a plaintext password against a stored hash.
     * Supports iRedMail password hash formats: {SSHA512}, {SSHA}, {CRYPT}, {PLAIN}, {SHA512}, {PLAIN-MD5}.
     */
    private static function verifyPassword(string $password, string $storedHash): bool
    {
        if (str_starts_with($storedHash, '{CRYPT}')) {
            $hash = substr($storedHash, 7);
            // @: crypt() deprecated in PHP 8.0 but required for verifying MD5/SHA-crypt hashes
            return hash_equals(@crypt($password, $hash), $hash);
        }

        if (str_starts_with($storedHash, '{SSHA512}')) {
            $decoded = base64_decode(substr($storedHash, 9));
            $originalHash = substr($decoded, 0, 64);
            $salt = substr($decoded, 64);
            $newHash = hash('sha512', $password . $salt, true);
            return hash_equals($originalHash, $newHash);
        }

        if (str_starts_with($storedHash, '{SSHA}')) {
            $decoded = base64_decode(substr($storedHash, 6));
            $originalHash = substr($decoded, 0, 20);
            $salt = substr($decoded, 20);
            $newHash = sha1($password . $salt, true);
            return hash_equals($originalHash, $newHash);
        }

        if (str_starts_with($storedHash, '{SHA512}')) {
            $decoded = base64_decode(substr($storedHash, 8));
            $newHash = hash('sha512', $password, true);
            return hash_equals($decoded, $newHash);
        }

        if (str_starts_with($storedHash, '{PLAIN-MD5}')) {
            return hash_equals(substr($storedHash, 11), md5($password));
        }

        if (str_starts_with($storedHash, '{PLAIN}')) {
            return $password === substr($storedHash, 7);
        }

        if (str_starts_with($storedHash, '$2')) {
            return password_verify($password, $storedHash);
        }

        // Bare MD5 hex (no prefix, 32 hex chars) — PLAIN-MD5 without prefix
        if (preg_match('/^[a-f0-9]{32}$/i', $storedHash)) {
            return hash_equals($storedHash, md5($password));
        }

        return hash_equals($storedHash, $password);
    }
}
