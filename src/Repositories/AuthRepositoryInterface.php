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

    /**
     * Whether the given email is a global admin.
     */
    public function isGlobalAdmin(string $email): bool;

    /**
     * Returns domains managed by the given admin email.
     *
     * @return string[]
     */
    public function getManagedDomains(string $email): array;
}
