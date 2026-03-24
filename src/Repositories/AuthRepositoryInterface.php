<?php

declare(strict_types=1);

namespace App\Repositories;

interface AuthRepositoryInterface
{
    /**
     * Authenticates a user by email and password.
     * Throws \Exception on failure (invalid credentials, not admin, etc.).
     */
    public function authenticate(string $email, string $password): bool;
}
