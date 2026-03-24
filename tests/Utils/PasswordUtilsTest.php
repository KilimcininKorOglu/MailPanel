<?php

declare(strict_types=1);

namespace Tests\Utils;

use App\Utils\PasswordUtils;
use PHPUnit\Framework\TestCase;

class PasswordUtilsTest extends TestCase
{
    public function testGenerateSsha512Password(): void
    {
        $hash = PasswordUtils::generateSsha512Password('TestPass1!');
        $this->assertStringStartsWith('{SSHA512}', $hash);
        $this->assertGreaterThan(20, strlen($hash));
    }

    public function testGenerateSha512Password(): void
    {
        $hash = PasswordUtils::generateSha512Password('TestPass1!');
        $this->assertStringStartsWith('{SHA512}', $hash);
    }

    public function testGenerateSshaPassword(): void
    {
        $hash = PasswordUtils::generateSshaPassword('TestPass1!');
        $this->assertStringStartsWith('{SSHA}', $hash);
    }

    public function testGenerateBcryptPassword(): void
    {
        $hash = PasswordUtils::generateBcryptPassword('TestPass1!');
        $this->assertStringStartsWith('{CRYPT}$2', $hash);
    }

    public function testGeneratePlainMd5Password(): void
    {
        $hash = PasswordUtils::generatePlainMd5Password('test');
        $this->assertSame(md5('test'), $hash);
    }

    public function testGenerateMd5Password(): void
    {
        $hash = PasswordUtils::generateMd5Password('test');
        $this->assertStringStartsWith('$1$', $hash);
    }

    public function testSsha512ProducesDifferentHashesWithSalt(): void
    {
        $hash1 = PasswordUtils::generateSsha512Password('same');
        $hash2 = PasswordUtils::generateSsha512Password('same');
        $this->assertNotSame($hash1, $hash2);
    }

    public function testSshaProducesDifferentHashesWithSalt(): void
    {
        $hash1 = PasswordUtils::generateSshaPassword('same');
        $hash2 = PasswordUtils::generateSshaPassword('same');
        $this->assertNotSame($hash1, $hash2);
    }

    public function testGenerateRandomPasswordLength(): void
    {
        $password = PasswordUtils::generateRandomPassword(20);
        $this->assertSame(20, strlen($password));
    }

    public function testGenerateRandomPasswordMinLength(): void
    {
        // Should respect Settings min length (default 8), but we request 16
        $password = PasswordUtils::generateRandomPassword(16);
        $this->assertGreaterThanOrEqual(16, strlen($password));
    }

    public function testGenerateRandomPasswordUniqueness(): void
    {
        $pw1 = PasswordUtils::generateRandomPassword();
        $pw2 = PasswordUtils::generateRandomPassword();
        $this->assertNotSame($pw1, $pw2);
    }

    public function testGenerateRandomPasswordContainsRequiredCategories(): void
    {
        // Default policy: lowercase, uppercase, numbers, special all required
        $password = PasswordUtils::generateRandomPassword(20);
        $this->assertMatchesRegularExpression('/[a-z]/', $password);
        $this->assertMatchesRegularExpression('/[A-Z]/', $password);
        $this->assertMatchesRegularExpression('/[0-9]/', $password);
        $this->assertMatchesRegularExpression('/[$@#%!^&*()\-_+={}[\]]/', $password);
    }
}
