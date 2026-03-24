<?php

declare(strict_types=1);

namespace App\Utils;

use App\Models\Settings;

class PasswordUtils
{
    /**
     * Main entry point for password hashing. Dispatches to the correct scheme.
     */
    public static function generatePasswordHash(string $password, ?string $scheme = null): string
    {
        $settings = Settings::getInstance();
        $password = trim($password);
        $scheme = $scheme ?? $settings->passwordDefaultScheme;

        return match ($scheme) {
            'BCRYPT' => self::generateBcryptPassword($password),
            'SSHA512' => self::generateSsha512Password($password),
            'SHA512' => self::generateSha512Password($password),
            'SSHA' => self::generateSshaPassword($password),
            'MD5' => '{CRYPT}' . self::generateMd5Password($password),
            'CRAM-MD5' => self::generatePasswordWithDoveadmPw('CRAM-MD5', $password),
            'PLAIN-MD5' => self::generatePlainMd5Password($password),
            'NTLM' => self::generatePasswordWithDoveadmPw('NTLM', $password),
            'PLAIN' => $settings->passwordHashesUsePrefixedScheme
                ? '{PLAIN}' . $password
                : $password,
            default => $password,
        };
    }

    public static function generateBcryptPassword(string $p): string
    {
        return '{CRYPT}' . password_hash($p, PASSWORD_BCRYPT);
    }

    public static function generateMd5Password(string $p): string
    {
        $salt = substr(base64_encode(random_bytes(6)), 0, 8);
        return crypt($p, '$1$' . $salt . '$');
    }

    public static function generatePlainMd5Password(string $p): string
    {
        return md5(trim($p));
    }

    public static function generateSshaPassword(string $p): string
    {
        $salt = random_bytes(8);
        $hash = sha1($p . $salt, true);
        return '{SSHA}' . base64_encode($hash . $salt);
    }

    public static function generateSha512Password(string $p): string
    {
        $hash = hash('sha512', $p, true);
        return '{SHA512}' . base64_encode($hash);
    }

    public static function generateSsha512Password(string $p): string
    {
        $salt = random_bytes(8);
        $hash = hash('sha512', $p . $salt, true);
        return '{SSHA512}' . base64_encode($hash . $salt);
    }

    /**
     * Generates password hash using external doveadm command.
     * Falls back to SSHA if doveadm is not available.
     */
    public static function generatePasswordWithDoveadmPw(string $scheme, string $password): string
    {
        $settings = Settings::getInstance();
        $scheme = strtoupper($scheme);
        $password = trim($password);

        $escapedPassword = escapeshellarg($password);
        $escapedScheme = escapeshellarg($scheme);

        $output = @shell_exec("doveadm pw -s {$escapedScheme} -p {$escapedPassword} 2>/dev/null");

        if ($output === null || $output === '') {
            return self::generateSshaPassword($password);
        }

        $pw = trim($output);

        if (!$settings->passwordHashesUsePrefixedScheme) {
            $pw = preg_replace('/^\{' . preg_quote($scheme, '/') . '\}/', '', $pw);
        }

        return $pw;
    }

    /**
     * Checks if a password hash uses a supported scheme.
     */
    public static function isSupportedPasswordScheme(string $pwHash): bool
    {
        if (!str_starts_with($pwHash, '{') || !str_contains($pwHash, '}')) {
            return false;
        }

        $scheme = strtoupper(substr($pwHash, 1, strpos($pwHash, '}') - 1));

        return in_array($scheme, [
            'PLAIN', 'CRYPT', 'MD5', 'PLAIN-MD5', 'SHA', 'SSHA',
            'SHA512', 'SSHA512', 'SHA512-CRYPT', 'BCRYPT', 'CRAM-MD5', 'NTLM',
        ], true);
    }
}
