<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\UserPassword;
use PHPUnit\Framework\TestCase;

class UserPasswordTest extends TestCase
{
    public function testValidPasswordReturnsNoErrors(): void
    {
        $errors = UserPassword::validate('Test1234!', 'Test1234!');
        $this->assertEmpty($errors);
    }

    public function testPasswordTooShort(): void
    {
        $errors = UserPassword::validate('Te1!', 'Te1!');
        $this->assertArrayHasKey('password', $errors);
        $this->assertStringContainsString('at least', $errors['password']);
    }

    public function testPasswordMissingDigit(): void
    {
        $errors = UserPassword::validate('TestPass!', 'TestPass!');
        $this->assertArrayHasKey('password', $errors);
        $this->assertStringContainsString('digit', $errors['password']);
    }

    public function testPasswordMissingUppercase(): void
    {
        $errors = UserPassword::validate('testpass1!', 'testpass1!');
        $this->assertArrayHasKey('password', $errors);
        $this->assertStringContainsString('uppercase', $errors['password']);
    }

    public function testPasswordMissingLowercase(): void
    {
        $errors = UserPassword::validate('TESTPASS1!', 'TESTPASS1!');
        $this->assertArrayHasKey('password', $errors);
        $this->assertStringContainsString('lowercase', $errors['password']);
    }

    public function testPasswordMissingSpecialChar(): void
    {
        $errors = UserPassword::validate('TestPass1', 'TestPass1');
        $this->assertArrayHasKey('password', $errors);
        $this->assertStringContainsString('special', $errors['password']);
    }

    public function testPasswordMismatch(): void
    {
        $errors = UserPassword::validate('Test1234!', 'Test1234@');
        $this->assertArrayHasKey('password_repeat', $errors);
        $this->assertStringContainsString('do not match', $errors['password_repeat']);
    }

    public function testNonAsciiCharacterRejected(): void
    {
        $errors = UserPassword::validate("Test1234!\xC3\xBC", "Test1234!\xC3\xBC");
        $this->assertArrayHasKey('password', $errors);
        $this->assertStringContainsString('ASCII', $errors['password']);
    }
}
