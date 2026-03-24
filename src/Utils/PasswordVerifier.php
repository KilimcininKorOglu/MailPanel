<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Verifies plaintext passwords against stored hashes.
 * Supports all iRedMail password hash formats.
 */
class PasswordVerifier
{
    public static function verify(string $password, string $storedHash): bool
    {
        if (str_starts_with($storedHash, '{CRYPT}')) {
            $hash = substr($storedHash, 7);
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

        if (preg_match('/^[a-f0-9]{32}$/i', $storedHash)) {
            return hash_equals($storedHash, md5($password));
        }

        return hash_equals($storedHash, $password);
    }
}
