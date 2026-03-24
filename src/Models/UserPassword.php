<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Password validation model. Returns validation errors as associative array.
 */
class UserPassword
{
    private const SPECIAL_CHARS = [
        '$', '@', '#', '%', '!', '^', '&', '*',
        '(', ')', '-', '_', '+', '=', '{', '}', '[', ']',
    ];

    /**
     * Validates password and password_repeat fields.
     *
     * @return array<string, string> Field-name to error-message pairs (empty if valid)
     */
    public static function validate(string $password, string $passwordRepeat): array
    {
        $settings = Settings::getInstance();
        $errors = [];

        // Validate password field
        $passwordErrors = self::validateSingle($password, $settings);
        if (!empty($passwordErrors)) {
            $errors['password'] = $passwordErrors;
        }

        // Validate password_repeat field
        $repeatErrors = self::validateSingle($passwordRepeat, $settings);
        if (!empty($repeatErrors)) {
            $errors['password_repeat'] = $repeatErrors;
        }

        // Check match (only if both pass individual validation or at least password passed)
        if ($password !== $passwordRepeat && !isset($errors['password_repeat'])) {
            $errors['password_repeat'] = 'Password and password confirmation do not match';
        }

        return $errors;
    }

    private static function validateSingle(string $password, Settings $settings): string
    {
        // ASCII check
        for ($i = 0; $i < strlen($password); $i++) {
            $ord = ord($password[$i]);
            if ($ord < 32 || $ord > 126) {
                return 'Password must contain only ASCII characters';
            }
        }

        // Min length
        if (strlen($password) < $settings->passwordMinLength) {
            return "Password must be at least {$settings->passwordMinLength} characters long";
        }

        // Digits
        if ($settings->passwordIncludesNumbers && !preg_match('/\d/', $password)) {
            return 'Password must contain at least one digit';
        }

        // Uppercase
        if ($settings->passwordIncludesUppercase && !preg_match('/[A-Z]/', $password)) {
            return 'Password must contain at least one uppercase letter';
        }

        // Lowercase
        if ($settings->passwordIncludesLowercase && !preg_match('/[a-z]/', $password)) {
            return 'Password must contain at least one lowercase letter';
        }

        // Special chars
        if ($settings->passwordIncludesSpecialChars) {
            $hasSpecial = false;
            for ($i = 0; $i < strlen($password); $i++) {
                if (in_array($password[$i], self::SPECIAL_CHARS, true)) {
                    $hasSpecial = true;
                    break;
                }
            }
            if (!$hasSpecial) {
                $chars = implode('', self::SPECIAL_CHARS);
                return "Password must contain at least one special character ($chars)";
            }
        }

        return '';
    }
}
